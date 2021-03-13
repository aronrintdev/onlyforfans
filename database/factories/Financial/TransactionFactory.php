<?php

namespace Database\Factories\Financial;

use App\Models\Financial\Account;
use App\Models\Financial\Transaction;
use Illuminate\Database\Eloquent\Factories\Factory;

class TransactionFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Transaction::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'account_id' => Account::factory(),
            'credit_amount' => 0,
            'debit_amount' => 0,
            'balance' => 0,
        ];
    }
}
