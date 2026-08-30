<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Livewire\Components\ContactSeller;
use App\Models\Message;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

/**
 * Regression test for a bug found while auditing the codebase: MessageSent
 * implemented ShouldBroadcast (queued), but the app never runs a queue
 * worker anywhere (README only documents `reverb:start`, QUEUE_CONNECTION
 * is `database`) — every broadcast job just sat in the `jobs` table
 * forever, so the recipient never received a message in real time, no
 * matter how long they kept the page open. Confirmed by hand: sending a
 * message left a row in `jobs` and the "temps réel" feature never fired.
 * Fixed by switching the event to ShouldBroadcastNow (dispatch inline,
 * no queue needed for the one broadcast this app has).
 *
 * Note: asserting an empty `jobs` table here wouldn't actually catch a
 * regression — phpunit.xml forces QUEUE_CONNECTION=sync for the whole
 * suite, so even the old queued ShouldBroadcast would dispatch inline
 * during tests and never touch that table either. The real distinguishing
 * fact — the one that broke in production — is the interface itself, so
 * that's what's asserted directly below.
 */
class MessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
    }

    public function test_sending_a_message_creates_it_in_the_database(): void
    {
        $seller = User::factory()->vendeur()->create(['is_active' => true]);
        $buyer = User::factory()->create(['is_active' => true]);
        $product = Product::factory()->for($seller, 'seller')->create();

        Livewire::actingAs($buyer)
            ->test(ContactSeller::class, ['product' => $product])
            ->set('content', "J'ai une question sur cet article.")
            ->call('send')
            ->assertSet('sent', true)
            ->assertSet('content', '');

        $this->assertDatabaseHas('messages', [
            'sender_id' => $buyer->id,
            'receiver_id' => $seller->id,
            'product_id' => $product->id,
            'content' => "J'ai une question sur cet article.",
        ]);
    }

    /** The actual regression: MessageSent must dispatch its broadcast inline, not through the queue. */
    public function test_message_sent_broadcasts_immediately_instead_of_being_queued(): void
    {
        $message = new MessageSent(new Message);

        $this->assertInstanceOf(ShouldBroadcastNow::class, $message);
        $this->assertNotInstanceOf(ShouldQueue::class, $message);
    }

    public function test_an_inactive_user_cannot_send_a_message(): void
    {
        $seller = User::factory()->vendeur()->create(['is_active' => true]);
        $buyer = User::factory()->create(['is_active' => false]);
        $product = Product::factory()->for($seller, 'seller')->create();

        $this->assertFalse($buyer->can('create', Message::class));
    }
}
