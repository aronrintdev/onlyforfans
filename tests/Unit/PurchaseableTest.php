<?php

namespace Tests\Unit;

use Money\Money;
use Money\Currency;
use Tests\TestCase;
use App\Models\Post;
use App\Models\User;
use App\Models\Timeline;
use App\Models\Financial\Transaction;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\IncompleteTestError;
use Tests\Helpers\Financial\AccountHelpers;
use App\Enums\Financial\TransactionTypeEnum;
use App\Enums\ShareableAccessLevelEnum;
use App\Events\AccessGranted;
use App\Events\AccessRevoked;
use App\Events\ItemPurchased;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

/**
 * Purchaseable Unit Test Cases
 *
 * @group unit
 * @group financial
 * @group purchaseable
 *
 * @package Tests\Unit
 */
class PurchaseableTest extends TestCase
{
    use RefreshDatabase;

    /* ------------------------------- Posts -------------------------------- */
    #region Posts

    /**
     * Verify price point works correctly
     *
     * @group post
     * @return void
     */
    public function test_verify_post_price_point()
    {
        // Create Post with Price
        $post = Post::factory()->pricedAt(1000)->create();

        $this->assertTrue($post->verifyPrice(1000));
        $money = new Money(1000, new Currency('USD'));
        $this->assertTrue($post->verifyPrice($money));

        $this->assertFalse($post->verifyPrice(800));
        $money = new Money(800, new Currency('USD'));
        $this->assertFalse($post->verifyPrice($money));
    }

    /**
     * Can purchase post
     *
     * @group post
     * @depends test_verify_post_price_point
     * @return void
     */
    public function test_can_purchase_post()
    {
        Event::fake([ ItemPurchased::class, AccessGranted::class ]);

        $post = Post::factory()->pricedAt(1000)->create();
        $purchaserAccounts = AccountHelpers::loadWallet(1000);

        $purchaserAccounts['internal']->purchase($post, 1000);

        $this->assertDatabaseHas(app(Transaction::class)->getTable(), [
            'account_id' => $purchaserAccounts['internal']->getKey(),
            'debit_amount' => 1000,
            'type' => TransactionTypeEnum::SALE,
            'resource_id' => $post->getKey(),
            'resource_type' => $post->getMorphString(),
        ], 'financial');

        Event::assertDispatched(ItemPurchased::class);
    }

    /**
     * Check that purchasing a post grants access to that post
     *
     * @group post
     * @depends test_can_purchase_post
     * @return void
     */
    public function test_purchase_post_grants_assess_to_post()
    {
        Event::fake([ItemPurchased::class, AccessGranted::class]);

        $post = Post::factory()->pricedAt(1000)->create();
        $purchaserAccounts = AccountHelpers::loadWallet(1000);
        $user = $purchaserAccounts['internal']->getOwner()->first();

        $purchaserAccounts['internal']->purchase($post, 1000);

        $this->assertDatabaseHas('shareables', [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $post->getMorphString(),
            'shareable_id' => $post->getKey(),
            'is_approved' => true,
            'access_level' => ShareableAccessLevelEnum::PREMIUM,
        ]);
        Event::assertDispatched(function (AccessGranted $event) use ($post, $user) {
            return $event->item->getKey() === $post->getKey() && $event->grantedTo === $user->getKey();
        });
    }

    /**
     * Chargeback disables access to post
     *
     * @group post
     * @depends test_purchase_post_grants_assess_to_post
     * @return void
     */
    public function test_chargeback_disables_post_access()
    {
        Event::fake([ItemPurchased::class, AccessGranted::class, AccessRevoked::class]);

        $post = Post::factory()->pricedAt(1000)->create();
        $purchaserAccounts = AccountHelpers::loadWallet(1000);
        $user = $purchaserAccounts['internal']->getOwner()->first();

        $purchaserAccounts['internal']->purchase($post, 1000);

        $purchaserAccounts['in']->handleChargeback($purchaserAccounts['transactions']['debit']);

        $this->assertDatabaseHas('shareables', [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $post->getMorphString(),
            'shareable_id' => $post->getKey(),
            'is_approved' => false,
            'access_level' => ShareableAccessLevelEnum::REVOKED,
        ]);

        Event::assertDispatched(function(AccessRevoked $event) use ($post, $user) {
            return $event->item->getKey() === $post->getKey() && $event->revokedFrom === $user->getKey();
        });
    }

    /**
     * Chargeback disables access to multiple posts made with chargeback transaction
     *
     * @group post
     * @depends test_chargeback_disables_post_access
     * @return void
     */
    public function test_chargeback_disables_multiple_posts_access()
    {
        Event::fake([ItemPurchased::class, AccessGranted::class, AccessRevoked::class]);

        $posts = Post::factory()->count(3)->pricedAt(1000)->create();
        $purchaserAccounts = AccountHelpers::loadWallet(3000);
        $user = $purchaserAccounts['internal']->getOwner()->first();

        $purchaserAccounts['internal']->purchase($posts[0], 1000);
        $purchaserAccounts['internal']->purchase($posts[1], 1000);
        $purchaserAccounts['internal']->purchase($posts[2], 1000);

        $purchaserAccounts['in']->handleChargeback($purchaserAccounts['transactions']['debit']);

        $this->assertDatabaseHas('shareables', [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $posts[0]->getMorphString(),
            'shareable_id' => $posts[0]->getKey(),
            'is_approved' => false,
            'access_level' => ShareableAccessLevelEnum::REVOKED,
        ]);

        $this->assertDatabaseHas('shareables', [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $posts[1]->getMorphString(),
            'shareable_id' => $posts[1]->getKey(),
            'is_approved' => false,
            'access_level' => ShareableAccessLevelEnum::REVOKED,
        ]);

        $this->assertDatabaseHas('shareables', [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $posts[2]->getMorphString(),
            'shareable_id' => $posts[2]->getKey(),
            'is_approved' => false,
            'access_level' => ShareableAccessLevelEnum::REVOKED,
        ]);

        Event::assertDispatched(AccessRevoked::class, 3);
    }

    /**
     * Chargeback disables access to multiple posts made with chargeback transaction
     *
     * @group post
     * @depends test_chargeback_disables_multiple_posts_access
     * @return void
     */
    public function test_chargeback_disables_multiple_posts_access_with_partial()
    {
        Event::fake([ItemPurchased::class, AccessGranted::class, AccessRevoked::class]);

        $posts = Post::factory()->count(3)->pricedAt(1000)->create();
        $purchaserAccounts = AccountHelpers::loadWallet(3500);
        $user = $purchaserAccounts['internal']->getOwner()->first();

        $purchaserAccounts['internal']->purchase($posts[0], 1000);
        $purchaserAccounts['internal']->purchase($posts[1], 1000);
        $purchaserAccounts['internal']->purchase($posts[2], 1000);

        $purchaserAccounts['in']->handleChargeback($purchaserAccounts['transactions']['debit']);

        $this->assertDatabaseHas('shareables', [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $posts[0]->getMorphString(),
            'shareable_id' => $posts[0]->getKey(),
            'is_approved' => false,
            'access_level' => ShareableAccessLevelEnum::REVOKED,
        ]);

        $this->assertDatabaseHas('shareables', [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $posts[1]->getMorphString(),
            'shareable_id' => $posts[1]->getKey(),
            'is_approved' => false,
            'access_level' => ShareableAccessLevelEnum::REVOKED,
        ]);

        $this->assertDatabaseHas('shareables', [
            'sharee_id' => $user->getKey(),
            'shareable_type' => $posts[2]->getMorphString(),
            'shareable_id' => $posts[2]->getKey(),
            'is_approved' => false,
            'access_level' => ShareableAccessLevelEnum::REVOKED,
        ]);

        Event::assertDispatched(AccessRevoked::class, 3);
    }

    #endregion

}