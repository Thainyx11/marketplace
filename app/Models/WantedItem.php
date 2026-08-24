<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'title', 'description', 'category_id', 'max_price', 'status'])]
class WantedItem extends Model
{
    public const STATUSES = ['ouverte', 'pourvue', 'fermee'];

    protected function casts(): array
    {
        return [
            'max_price' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return BelongsTo<Category, $this> */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /** @return HasMany<WantedItemResponse, $this> */
    public function responses(): HasMany
    {
        return $this->hasMany(WantedItemResponse::class);
    }

    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', 'ouverte');
    }
}
