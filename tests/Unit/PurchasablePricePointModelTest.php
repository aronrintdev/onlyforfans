<?php

namespace Tests\Unit;

use App\Enums\ShareableAccessLevelEnum;
use App\Models\Post;
use App\Models\PurchasablePricePoint;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use SebastianBergmann\RecursionContext\InvalidArgumentException;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\IncompleteTestError;
use Tests\TestCase;
use Throwable;

/**
 * Unit tests for the  PurchasablePricePoint Model
 *
 * @group unit
 * @group price-points
 * @group purchasable-price-points
 *
 * @package Tests\Unit
 */
class PurchasablePricePointModelTest extends TestCase
{
    use RefreshDatabase;


    /**
     * getDefaultFor() works
     * @return void
     */
    public function test_getDefaultFor_gets_default()
    {
        $post = Post::factory()->pricedAt(1000)->create();

        $this->assertDatabaseHas(app(PurchasablePricePoint::class)->getTableName(), [
            'purchasable_id' => $post->getKey(),
            'price' => 1000,
            'currency' => PurchasablePricePoint::getDefaultCurrency(),
            'current' => true,
            'active' => true,
            'available_at' => null,
            'expires_at' => null,
            'access_level' => ShareableAccessLevelEnum::PREMIUM,
        ]);

        $pricePoint = PurchasablePricePoint::getDefaultFor($post, 1000);

        $this->assertCurrencyAmountIsEqual(1000, $pricePoint->price);
        $this->assertEquals($post->getKey(), $pricePoint->purchasable_id);
        $this->assertNull($pricePoint->available_at);
        $this->assertNull($pricePoint->expires_at);
        $this->assertNull($pricePoint->available_with);
        $this->assertEquals(ShareableAccessLevelEnum::PREMIUM ,$pricePoint->access_level);
        $this->assertTrue($pricePoint->current);
        $this->assertTrue($pricePoint->active);
    }

    /**
     * getDefaultFor() works
     * @return void
     */
    public function test_getDefaultFor_creates_new_default()
    {
        $post = Post::factory()->pricedAt(1000)->create();

        $pricePoint = PurchasablePricePoint::getDefaultFor($post, 900);

        $this->assertDatabaseHas($pricePoint->getTableName(), [
            'purchasable_id' => $post->getKey(),
            'price' => 900,
            'currency' => PurchasablePricePoint::getDefaultCurrency(),
            'current' => false,
            'active' => false,
            'available_at' => null,
            'expires_at' => null,
            'access_level' => ShareableAccessLevelEnum::PREMIUM,
        ]);

        $this->assertCurrencyAmountIsEqual(900, $pricePoint->price);
        $this->assertEquals($post->getKey(), $pricePoint->purchasable_id);
        $this->assertNull($pricePoint->available_at);
        $this->assertNull($pricePoint->expires_at);
        $this->assertNull($pricePoint->available_with);
        $this->assertEquals(ShareableAccessLevelEnum::PREMIUM, $pricePoint->access_level);
        $this->assertFalse($pricePoint->current);
        $this->assertFalse($pricePoint->active);
    }

    /**
     * saveAsCurrentDefault() works
     * @return void
     */
    public function test_saveAsCurrentDefault_set_price_point_as_new_default()
    {
        $post = Post::factory()->pricedAt(1000)->create();

        $this->assertDatabaseHas(app(PurchasablePricePoint::class)->getTableName(), [
            'purchasable_id' => $post->getKey(),
            'price' => 1000,
            'currency' => PurchasablePricePoint::getDefaultCurrency(),
            'current' => true,
            'active' => true,
            'available_at' => null,
            'expires_at' => null,
            'access_level' => ShareableAccessLevelEnum::PREMIUM,
        ]);

        PurchasablePricePoint::getDefaultFor($post, 900)->saveAsCurrentDefault();

        $this->assertDatabaseHas(app(PurchasablePricePoint::class)->getTableName(), [
            'purchasable_id' => $post->getKey(),
            'price' => 1000,
            'currency' => PurchasablePricePoint::getDefaultCurrency(),
            'current' => false,
            'active' => false,
            'available_at' => null,
            'expires_at' => null,
            'access_level' => ShareableAccessLevelEnum::PREMIUM,
        ]);

        $this->assertDatabaseHas(app(PurchasablePricePoint::class)->getTableName(), [
            'purchasable_id' => $post->getKey(),
            'price' => 900,
            'currency' => PurchasablePricePoint::getDefaultCurrency(),
            'current' => true,
            'active' => true,
            'available_at' => null,
            'expires_at' => null,
            'access_level' => ShareableAccessLevelEnum::PREMIUM,
        ]);
    }


}