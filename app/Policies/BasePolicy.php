<?php

namespace App\Policies;

use App\User;
use Exception;
use ReflectionMethod;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use App\Policies\Enums\PolicyValidation;
use Illuminate\Auth\Access\HandlesAuthorization;

class BasePolicy
{
    use HandlesAuthorization;

    /**
     * Policy definitions
     */
    protected $policies = [];

    public $permissionOnly = 'permissionOnly';

    /**
     * Overwrite for specific name for this model.
     */
    protected $permissionName = null;

    /**
     * Will skip automatic checking of permissions if set to false.
     */
    protected $usePermissions = true;

    /**
     * Any function name in this array will skip permission checking
     */
    protected $skipPermissionsOn = [];

    /**
     * Array of overrides action permission names from methods, use if you need a different name than the method name.
     */
    protected $actionNames = [];

    public function __call($method, $arguments) {
        // dd($method, $arguments);
        if ( method_exists($this, $method) || Arr::has($this->policies, $method) ) {
            if ($this->usePermissions && ! in_array($method, $this->skipPermissionsOn)) {
                if ($this->hasPermission($method, $arguments[0], $arguments[1] ?? null)) {
                    // If has permission then return true.
                    return true;
                }
            }
            if (Arr::has($this->policies, $method)) {
                $policies = Str::of($this->policies[$method])->explode(' ')->all();
                foreach($policies as $policy) {
                    if ($policy === $this->permissionOnly) {
                        return false;
                    }
                    $policy = Str::of($policy)->explode(':')->all();
                    if ( method_exists($this, $policy[0]) ) {
                        $result = call_user_func_array(array($this, $policy[0]), $arguments);
                        if ($result === true) {
                            if (isset($policy[1])) {
                                if ( $policy[1] == PolicyValidation::PASS ) {
                                    return true;
                                } else if ($policy[1] == PolicyValidation::FAIL) {
                                    return false;
                                }
                            }
                        } else { // $result !== true
                            if (isset($policy[2])) {
                                if ( $policy[2] == PolicyValidation::PASS ) {
                                    return true;
                                } else if ($policy[2] == PolicyValidation::FAIL) {
                                    return false;
                                }
                            }
                        }
                    }
                }
            }
            if ( method_exists($this, $method) ) {
                $reflection = new ReflectionMethod($this, $method);
                // To avoid the private infinite loop
                if ($reflection->isProtected() === false) {
                    throw new Exception('Method ' . $method . ' is not a protected method on ' . class_basename($this));
                }
                return call_user_func_array(array($this, $method), $arguments);
            } else if( method_exists($this, $method . 'Policy') ) {
                $reflection = new ReflectionMethod($this, $method . 'Policy');
                // To avoid the private infinite loop
                if ( $reflection->isPrivate() === true) {
                    throw new Exception('Method ' . $method . 'Policy' . ' is a private method on ' . class_basename($this));
                }
                return call_user_func_array(array($this, $method . 'Policy'), $arguments);
            }
            // No method, but in policies array, assume false.
            return false;
        }
        throw new Exception('Method ' . $method . ' does not exist on ' . class_basename($this) . ' or is not a defined policy');
    }

    /**
     * Checks if user has a permission to perform this action on the model.
     * @param  User  $user
     * @param  object  $model | The model associated with this permission.
     * @param  string  $action
     */
    public function hasPermission(string $action, User $user, $model = null) : bool
    {
        if ( ! isset($user)) {
            return false;
        }
        if ( ! isset($this->permissionName) ) {
            if (isset($model)) {
                $this->permissionName = class_basename($model);
            } else {
                // Remove Policy from class name | 'PostPolicy' => 'Post'
                $this->permissionName = Str::replaceLast('Policy', '', class_basename($this));
            }
        }

        if (in_array($action, $this->actionNames)) {
            $action = $this->actionNames[$action];
        }

        if (isset($model)) {
            // Check for specific permission
            // {model}.{id}.{action}
            // ex. `Post.2.update`
            return $user->hasPermissionTo($this->permissionName . '.' . $action . '.' . $model->getKey());
        }

        // No model key in this case.
        // {model}.{action}
        // example `Post.viewAny`
        return $user->hasPermissionTo($this->permissionName . '.' . $action);
    }
}

