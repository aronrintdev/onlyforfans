<?php

namespace Database\Factories\Financial;

use App\Enums\Financial\AccountTypeEnum;
use App\Models\Financial\Account;
use App\Models\User;
use Illuminate\Support\Facades\Config;
use Illuminate\Database\Eloquent\Factories\Factory;

class AccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Account::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        $defaultSystem = Config::get('transactions.default');
        $defaultCurrency = Config::get('transactions.defaultCurrency', 'USD');

        $user = new User();

        return [
            'system' => $defaultSystem,
            'owner_type' => $user->getMorphString(),
            'owner_id' => User::factory(),
            'name' => $this->faker->name() . ' Internal Account',
            'type' => AccountTypeEnum::INTERNAL,
            'currency' => $defaultCurrency,
            'verified' => true,
            'can_make_transactions' => true
        ];
    }

    /**
     * Configure the model factory.
     *
     * @return $this
     */
    public function configure()
    {
        return $this->afterMaking(function (Account $account) {
            $account->name = $this->makeAccountName($account);
        })->afterCreating(function (Account $account) {
            $account->name = $this->makeAccountName($account);
            $account->save();
        });
    }

    /**
     * Create account name string
     *
     * @return  string
     */
    public function makeAccountName(Account $account): string
    {
        return $account->owner->username . ' ' . AccountTypeEnum::stringify($account->type);
    }

    /**
     * Set Account financial system
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function system(string $system)
    {
        return $this->state(function (array $attributes) use ($system) {
            return [ 'system' => $system, ];
        });
    }

    /**
     * Set Account currency
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function currency(string $currency)
    {
        return $this->state(function (array $attributes) use ($currency) {
            return [ 'currency' => $currency, ];
        });
    }

    /**
     * Set Account type to `in`
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function asTypeIn()
    {
        return $this->state(function (array $attributes) {
            return [ 'type' => AccountTypeEnum::IN, ];
        });
    }

    /**
     * Set Account type to `internal`
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function asTypeInternal()
    {
        return $this->state(function (array $attributes) {
            return [ 'type' => AccountTypeEnum::INTERNAL, ];
        });
    }

    /**
     * Set Account type to `out`
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function asTypeOut()
    {
        return $this->state(function (array $attributes) {
            return [ 'type' => AccountTypeEnum::OUT, ];
        });
    }

    /**
     * Set account as verified
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function verified()
    {
        return $this->state(function (array $attributes) {
            return ['verified' => true,];
        });
    }

    /**
     * Set account as not verified
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function unverified()
    {
        return $this->state(function (array $attributes) {
            return [ 'verified' => false, ];
        });
    }

    /**
     * Set as can make transactions
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function transactionsAllowed()
    {
        return $this->state(function (array $attributes) {
            return ['can_make_transactions' => true,];
        });
    }

    /**
     * Set as cannot make transactions
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function transactionsBlocked()
    {
        return $this->state(function (array $attributes) {
            return [ 'can_make_transactions' => false, ];
        });
    }
}
