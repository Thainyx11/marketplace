<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['wanted_item_id', 'seller_id', 'content', 'product_id'])]
class WantedItemResponse extends Model
{
    /** @return BelongsTo<WantedItem, $this> */
    public function wantedItem(): BelongsTo
    {
        return $this->belongsTo(WantedItem::class);
    }

    /** @return BelongsTo<User, $this> */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
