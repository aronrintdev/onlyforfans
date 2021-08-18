<?php

namespace App\Models\Financial;

use Carbon\Carbon;
use App\Models\Casts\Money;
use App\Models\Traits\UsesUuid;
use App\Models\Financial\Traits\HasCurrency;
use App\Models\Financial\Traits\HasSystemByAccount;
use App\Enums\Financial\TransactionSummaryTypeEnum as Type;
use Illuminate\Support\Collection;

class TransactionSummary extends Model
{
    use UsesUuid,
        HasSystemByAccount;

    protected $connection = 'financial';
    protected $table = 'transaction_summaries';

    protected $forceCombV4Uuid = true;

    /**
     * We should not be filling anything from the UI here, All UI interactions should be read-only.
     */
    protected $guarded = [];

    #region casts
    protected $casts = [
        'balance'        => Money::class,
        'balance_delta'  => Money::class,
        'credit_sum'     => Money::class,
        'debit_sum'      => Money::class,
        'credit_average' => Money::class,
        'debit_average'  => Money::class,
        'stats'          => 'collection',
    ];

    public function getCurrencyAttribute()
    {
        return $this->account->currency;
    }

    #endregion

    /* ---------------------------- Relationships --------------------------- */
    #region Relationships
    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    public function from_transaction()
    {
        return $this->belongsTo(Transaction::class, 'from_transaction_id');
    }

    public function to_transaction()
    {
        return $this->belongsTo(Transaction::class, 'to_transaction_id');
    }

    public function latestSettled()
    {
        return $this->to()->max('settled_at');
    }

    #endregion

    /* ------------------------------- Scopes ------------------------------- */
    #region Scopes

    public function scopeType($query, $type) {
        return $query->where('type', $type);
    }

    public function scopeIsAccount($query, $account) {
        if ($account instanceof Account) {
            return $query->where('account_id', $account->id);
        }
        return $query->where('account_id', $account);
    }

    #endregion Scopes

    /* ------------------------------ Functions ----------------------------- */
    #region Functions

    /**
     * Creates a new un-finalized Transaction Summary
     *
     * @param  Account  $account
     * @param  Type  $type
     * @param  array  $options
     * @return  TransactionSummary
     */
    public static function start(Account $account, $type = Type::CUSTOM, $options = [] ): TransactionSummary
    {
        return TransactionSummary::create(array_merge([
            'account_id' => $account->getKey(),
            'type' => $type,
            'finalized' => false,
        ], $options));
    }

    /**
     * Get the latest finalized Summary for an account. Latest is latest to_transaction settled_at
     *
     * @param  Account  $account
     * @return  TransactionSummary
     */
    public static function lastFinalized(Account $account)
    {
        return TransactionSummary::select(TransactionSummary::getTableName() . '.*')
            ->join(Transaction::getTableName(), Transaction::getTableName() . '.id', '=', TransactionSummary::getTableName() . '.to_transaction_id' )
            ->where('finalized', true)
            ->orderByDesc(Transaction::getTableName() . '.settled_at')
            ->limit(1);
    }

    public static function getBatch(Account $account, string $unit, Carbon $from, Carbon $to)
    {
        // TODO
    }

    /**
     * Gets an amount of TransactionsSummaries from type unit
     *
     * @param Account $account
     * @param string $unit
     * @param int $amount
     * @return Collection Collection of the items
     */
    public static function getBatchAgo(Account $account, string $unit, int $amount)
    {
        $type = static::switchUnitToEnum($unit);

        switch ($type) {
            case Type::DAILY:
                $start = Carbon::now()->startOfDay()->subDays($amount);
                break;
            case Type::WEEKLY:
                $start = Carbon::now()->startOfWeek()->startOfDay()->subWeeks($amount);
                break;
            case Type::MONTHLY:
                $start = Carbon::now()->startOfMonth()->startOfDay()->subMonths($amount);
                break;
            case Type::YEARLY:
                $start = Carbon::now()->startOfYear()->startOfDay()->subYear($amount);
                break;
            default:
                $start = Carbon::now();
        }

        return TransactionSummary::isAccount($account)->type($type)->where('from', '>=', $start)->get();
    }

    /**
     * Switch Carbon units to TransactionSummaryTypeEnum values
     */
    public static function switchUnitToEnum(string $unit): string
    {
        switch ($unit) {
            case 'day': return Type::DAILY;
            case 'week': return Type::WEEKLY;
            case 'month': return Type::MONTHLY;
            case 'year': return Type::YEARLY;
            // If Enum value was passed in
            default: return $unit;
        }
    }

    #endregion

}
