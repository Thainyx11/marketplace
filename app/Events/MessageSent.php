<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

// FIX: was ShouldBroadcast (queued) — nothing in this app ever runs `queue:work`
// (QUEUE_CONNECTION=database, no worker documented or started anywhere), so the
// broadcast job just sat in the `jobs` table forever and the recipient never got
// the live message. ShouldBroadcastNow dispatches synchronously within the same
// request, which is all a single queued job (this one) needs.
class MessageSent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public Message $message) {}

    /** @return array<int, PrivateChannel> */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel(Message::threadChannel(
                $this->message->product_id,
                $this->message->sender_id,
                $this->message->receiver_id,
            )),
        ];
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->message->id,
            'sender_id' => $this->message->sender_id,
            'sender_name' => $this->message->sender->name,
            'content' => $this->message->content,
            'created_at' => $this->message->created_at->format('H:i'),
        ];
    }

    public function broadcastAs(): string
    {
        return 'message.sent';
    }
}
