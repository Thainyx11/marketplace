<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

#[Fillable(['product_id', 'path', 'position'])]
class ProductImage extends Model
{
    /** @return BelongsTo<Product, $this> */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    /**
     * Seeded/demo images store a full external URL directly in `path`;
     * uploaded images store a local disk path resolved via Storage::url().
     */
    protected function url(): Attribute
    {
        return Attribute::make(
            get: fn () => Str::startsWith($this->path, ['http://', 'https://'])
                ? $this->path
                : Storage::url($this->path),
        );
    }
}
