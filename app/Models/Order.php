<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable(['buyer_id', 'promo_code_id', 'discount_amount', 'total', 'status', 'shipping_address'])]
class Order extends Model
{
    /** Statuses in pipeline order, per the cahier des charges section 4.3. */
    public const STATUSES = ['en_attente', 'acceptee', 'expediee', 'livree'];

    protected function casts(): array
    {
        return [
            'discount_amount' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /** @return BelongsTo<PromoCode, $this> */
    public function promoCode(): BelongsTo
    {
        return $this->belongsTo(PromoCode::class);
    }

    /** @return HasMany<OrderItem, $this> */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /** @return HasOne<Payment, $this> */
    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    /**
     * The order's buyer-facing status only advances once every line item
     * (which may belong to different independent sellers) has reached it.
     */
    public function recomputeStatus(): void
    {
        $itemStatuses = $this->items()->pluck('status');

        $status = collect(self::STATUSES)
            ->reverse()
            ->first(fn (string $candidate) => $itemStatuses->every(
                fn (string $s) => array_search($s, self::STATUSES) >= array_search($candidate, self::STATUSES)
            )) ?? 'en_attente';

        if ($status !== $this->status) {
            $this->update(['status' => $status]);
        }
    }
}
