<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['code', 'type', 'value', 'max_uses', 'used_count', 'expires_at', 'active'])]
class PromoCode extends Model
{
    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'expires_at' => 'datetime',
            'active' => 'boolean',
        ];
    }

    public function isValid(): bool
    {
        if (! $this->active) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses !== null && $this->used_count >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function discountFor(float $amount): float
    {
        return $this->type === 'percent'
            ? round($amount * ((float) $this->value / 100), 2)
            : min((float) $this->value, $amount);
    }
}
