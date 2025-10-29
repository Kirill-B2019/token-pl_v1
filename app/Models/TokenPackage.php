<?php
// |KB Пакеты токенов: цена, скидка, вычисление итоговой стоимости и сортировка

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TokenPackage extends Model
{
    protected $fillable = [
        'name',
        'description',
        'token_amount',
        'price',
        'discount_percentage',
        'is_active',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'token_amount' => 'decimal:8',
            'price' => 'decimal:8',
            'discount_percentage' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Calculate final price with discount
     */
    public function getFinalPriceAttribute(): float
    {
        if ($this->discount_percentage > 0) {
            return $this->price * (1 - $this->discount_percentage / 100);
        }
        
        return $this->price;
    }

    /**
     * Calculate savings amount
     */
    public function getSavingsAmountAttribute(): float
    {
        return $this->price - $this->final_price;
    }

    /**
     * Scope for active packages
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope for ordered packages
     */
    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }
}
