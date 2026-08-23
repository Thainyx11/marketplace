<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sender_id', 'receiver_id', 'product_id', 'content', 'seen'])]
class Message extends Model
{
    protected function casts(): array
    {
        return [
            'seen' => 'boolean',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    /** @return BelongsTo<User, $this> */
    public function receiver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'receiver_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /** @return HasMany<MessageReport, $this> */
    public function reports(): HasMany
    {
        return $this->hasMany(MessageReport::class);
    }

    public static function threadChannel(int $productId, int $userIdA, int $userIdB): string
    {
        $ids = [$userIdA, $userIdB];
        sort($ids);

        return "chat.{$productId}.{$ids[0]}.{$ids[1]}";
    }
}
