# Adding Model Polices

1. Extend the `BasePolicy`
2. Include any of the traits you need.
3. Create a Addon Policies Array *See below*
4. Create any custom policy action methods
   1. All methods you are using should be `protected function {Action Name}` or `public function {Action Name}Policy`.
      Do not use private access for policy functions, using public for `{Action Name}` such as `public function update()`
      will cause permission checking and other plugin policies to not run. If you want to use public access modifiers
      use `{Action Name}Policy` for the function name. Such as `public function updatePolicy()` for a policy
      of `update`.

## Order of Rules
Policy rules will be checked in this order.
1. Direct permission to action.
   1. If user has permission then will return true without checking other rules.
      1. Think of having direct permission as an override.
2. Addon Policies Array, *see below*.
3. Defined policy function
   1. Will try `protected function {Policy Name}()` first
   2. Then `public function {Policy Name}Policy()` next
   3. If you defined a policy in the `$policies` array but did not define a function then and we got to this point, then
      the access will be considered forbidden.

## Permission checking
- You can turn permissions checking off for the entire class with:
```
protected $usePermissions = false;
```
- If there is a specific policy you do not want permissions to run on provide its name here:
```
protected $skipPermissionsOn = [];
```
#### Policy Permission Action Names
Normally permissions will be saved like such.
```
   {Model Name}.{action}.{identifier}
   or
   {Model Name}.{action}
```
where action is the name of the policy action, but if you want the action to be named something different in the
resulting permission. Then you can use `protected $actionNames = [];` to change the name that gets put or read from the
permissions.

Example:
```
protected $actionNames = [
   'tip' => 'tippable',
];
```
will result in the permission being named `Post.tippable.4` instead of `Post.tip.4`


## Addon Policies Array
You may set your addon policies on this array.
```
protected $policies = [];
```
#### Syntax
```
protected $policies = [
   '{nameOfPolicy}' => '{addon policy name}:{true action}:{false action} {additional addon Policies}',
]
```
- Multiple addon policies are separated by a space "` `"

#### Actions
The actions are defined by `App\Policies\Enums\PolicyValidation` Enum. They are `pass`, `fail`, and `next`. If you do
not specify an action it will be assumed to be `next`.
- `pass` will automatically pass the policy without continuing **Warning:** keep in mind no further policies will be checked.
- `fail` will automatically pass the policy without continuing **Warning:** keep in mind no further policies will be checked.
- `next` will continue to the next policy without. Use this if you want multiple policies to be valid. *This is the
  default option picked if no action is specified.*

*Note:* While just having the name of the policy is valid. It is pointless as it is the same as `policy:next:next` and
will be run but do nothing.

#### Order
When using multiple addon policies they will run in the order that is specified.
```
protected $policies = [
   'view' => 'isAdmin:pass isBlockedByOwner:fail',
]
```
If `isAdmin` returns `true` then `isBlockedByOwner` will not run.

#### Permission Only
There is a special case police `permissionOnly` use the BasePolicy constant `permissionOnly` to indicated that the action is
only permitted via permissions.
```
protected $policies = [
   'viewAny' => 'permissionOnly',
];
```

#### Custom Addon Policies
The name of the addonPolicy is the same as its name provide. Addon policies must return a boolean.
You can use this if multiple policies use the same function.
```
protected $policies = [
   'view' => 'myAddonPolicy:pass:fail'
]

public function myAddonPolicy($user, $model) : bool
{
   //
}
```

#### Examples
```
protected $policies = [
   'view' => 'isBlockedByOwner:fail:next',
   'update' => 'isOwner:pass:next',
]
```
also valid
```
protected $policies = [
   'view' => 'isBlockedByOwner:fail',
   'update' => 'isOwner:pass',
]
```





## Basic Crud Naming Rules

Using Laravel's Basic Crud Naming conventions. Note, some actions have the same policy as you cannot do one without the
other, such as edit and store.

| Action        | Policy Name   | Method passes model | Details                                                                                                                                                                                                              |
|---------------|---------------|---------------------|----------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `index`       | `viewAny`     | `false`             | Viewing any / index of the model, many times this is an admin only action as we do not want to expose entire tables of data to any user. For personalized datasets such as dashboards use a custom action and policy |
| `create`      | `create`      | `false`             | Creating a new resource.                                                                                                                                                                                             |
| `store`       | `create`      | `false`             | Storing a new resource in DB same policy as create.                                                                                                                                                                  |
| `show`        | `view`        | `true`              | Viewing this particular resource.                                                                                                                                                                                    |
| `edit`        | `update`      | `true`              | Editing this particular resource, same policy as updating.                                                                                                                                                           |
| `update`      | `update`      | `true`              | Updating / saving a specific resource.                                                                                                                                                                               |
| `delete`      | `delete`      | `true`              | Deleting a specific resource.                                                                                                                                                                                        |
| `forceDelete` | `forceDelete` | `true`              | Permanently delete a model with soft deletes                                                                                                                                                                         |
| `restore`     | `restore`     | `true`              | Restore a model that has soft deletes from trash                                                                                                                                                                     |

#### Method passes model
If true the model is passed along with the user.

Example:
```
protected function create(User $user)
{
   //
}
protected function view(User $user, Post $post)
{
   //
}
```

## Permission
When giving out permission for policies, use wildcard syntax where necessary.
Examples:
```
'Post.*'
'Post.update.*'
'Post.show.*'
'Post.create'
'Post.show.5'
```
**Warning**: `*` means all, not any.
`Post.*` and `Post.update.*` will both be true for `Post.update.5`

---

Notes:

Erik Hattervig  12 minutes ago
If you have 'update' => 'isOwner:pass' in the policies array then the update function won't be entered if the user owns the resource. The policy array rule come before that the function and is basically for common functions that should pass or fail. If you want to enter the function only if a user is an owner you can put 'update' => 'isOwner:next:fail' That will auto fail any non Owners, but then move onto the update function for owners.

Erik Hattervig  10 minutes ago
Sorry if that wasn't the clearest, you can also enter the protected function all the time if you don't have an 'update' entry in the policies array.

Peter Gorgone  9 minutes ago
ok, but why did changing the ‘update’ policy method to ‘public’ result it in being called, even with ‘update’ => ‘isOwner:pass’ in the array?

Peter Gorgone  9 minutes ago
is that a valid use case for the lib?

Erik Hattervig  5 minutes ago
Has to do with the way the php function __call works. __call catches all unknown function calls. So if you set the function to public it won't ever go through the __call in basePolicy. That is way laravel model attributs are named things like getNameAttribute. Other wise __call would not work for the attribute. (edited) 

Peter Gorgone  3 minutes ago
ok so create view update delete, etc should be protected…but if we want to add ‘like’ or ‘follow’…they should be public…is this correct?

Peter Gorgone  2 minutes ago
…minding the array rules as you stated

Erik Hattervig  1 minute ago
If you use it like this $user->can('action', $resource) Then it needs to be protected.

Erik Hattervig  1 minute ago
So, like and follow should be protected.

Most every function you put on a policy will be protected. The statement about public functions is for stuff on the left side of the policies array, such as isOwner and isBlockedByOwner, but most of the time those will be added by a trait as they are meant to be functions that we know are going to be reused a bunch of times.
