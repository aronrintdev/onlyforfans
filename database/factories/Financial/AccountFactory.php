<?php

namespace Database\Factories\Financial;

use Carbon\Carbon;
use App\Models\User;
use App\Models\Financial\Account;
use Illuminate\Support\Facades\Config;
use App\Enums\Financial\AccountTypeEnum;
use App\Models\Financial\SegpayCard;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Collection;

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
            'balance' => 0,
            'balance_last_updated_at' => Carbon::now(),
            'pending' => 0,
            'pending_last_updated_at' => Carbon::now(),
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

    public function forUser(User $user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'owner_type' => $user->getMorphString(),
                'owner_id' => $user->getKey(),
            ];
        });
    }

    public function withResource($resource)
    {
        return $this->state(function (array $attributes) use ($resource) {
            return [
                'resource_type' => $resource->getMorphString(),
                'resource_id' => $resource->getKey(),
            ];
        });
    }

    public function withCard($card = null)
    {
        return $this->state(function (array $attributes) use ($card) {
            return [
                'resource_type' => $card ? $card->getMorphString() : app(SegpayCard::class)->getMorphString(),
                'resource_id' => $card ? $card : SegpayCard::factory(),
            ];
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
    public function asIn()
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
    public function asInternal()
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
    public function asOut()
    {
        return $this->state(function (array $attributes) {
            return [ 'type' => AccountTypeEnum::OUT, ];
        });
    }

    /**
     * Set account balance
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withBalance(int $balance)
    {
        return $this->state(function (array $attributes) use($balance) {
            return [
                'balance' => $balance,
                'balance_last_updated_at' => Carbon::now(),
            ];
        });
    }

    /**
     * Set account pending balance
     *
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function withPending(int $pending)
    {
        return $this->state(function (array $attributes) use ($pending) {
            return [
                'pending' => $pending,
                'pending_last_updated_at' => Carbon::now(),
            ];
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

    /**
     * Sets the owner to be the same as passed account
     *
     * @param Account $account
     * @return  \Illuminate\Database\Eloquent\Factories\Factory
     */
    public function sameOwnerAs(Account $account)
    {
        return $this->state(function (array $attributes) use ($account) {
            return [
                'owner_type' => $account->getOwner()->first()->getMorphString(),
                'owner_id' => $account->getOwner()->first()->getKey(),
            ];
        });
    }

}
