<?php

namespace Tests\Unit\Financial;

use Carbon\Carbon;
use App\Models\Financial\PayoutBatch;
use Illuminate\Support\Facades\Config;
use Tests\traits\Financial\NoHoldPeriod;
use App\Enums\Financial\PayoutBatchTypeEnum as BatchType;

/**
 * @group unit
 * @group financial
 * @group payouts
 *
 * @package Tests\Unit\Financial
 */
class PayoutBatchModelTest extends TestCase
{
    use NoHoldPeriod;

    public function setUp(): void
    {
        parent::setUp();
        $this->rolloverTime = Carbon::now()->next(Config::get('payout.batch.rollover.time', '00:00'));
    }

    /**
     * The next time a payout is suppose to rollover
     *
     * @var Carbon
     */
    private $rolloverTime;

    /**
     * currentBatch() should create a new record
     */
    public function test_get_current_batch_makes_new_batch()
    {
        $this->travelTo((clone $this->rolloverTime)->subHour());

        // Gets new batch when non are there already
        $batch = PayoutBatch::currentBatch(BatchType::SEGPAY_ACH);

        $this->assertDatabaseHas(PayoutBatch::getTableName(), [
            'type' => BatchType::SEGPAY_ACH,
            'collect_until' => (clone $this->rolloverTime),
            'settled_at' => null,
        ], $this->getConnectionString());

        $batch->delete();
        $this->travelBack();
    }

    /**
     * @depends test_get_current_batch_makes_new_batch
     */
    public function test_get_current_batch_gets_current_batch()
    {
        $this->travelTo((clone $this->rolloverTime)->subHour());

        $batch = PayoutBatch::currentBatch(BatchType::SEGPAY_ACH);

        $this->travel(5)->minutes();

        $newBatch = PayoutBatch::currentBatch(BatchType::SEGPAY_ACH);

        $this->assertEquals($batch->getKey(), $newBatch->getKey());

        $batch->delete();
        $this->travelBack();
    }

    /**
     * @depends test_get_current_batch_makes_new_batch
     */
    public function test_get_current_batch_preps_next_batch()
    {
        $this->travelTo((clone $this->rolloverTime)->subHour());
        $batch = PayoutBatch::currentBatch(BatchType::SEGPAY_ACH);

        $this->travelTo((clone $this->rolloverTime)->subMinute());

        $batch = PayoutBatch::currentBatch(BatchType::SEGPAY_ACH);

        $this->assertDatabaseHas(PayoutBatch::getTableName(), [
            'type' => BatchType::SEGPAY_ACH,
            'collect_until' => (clone $this->rolloverTime)->addDay(),
            'settled_at' => null,
        ], $this->getConnectionString());

        $batch->delete();
        $this->travelBack();
    }
}