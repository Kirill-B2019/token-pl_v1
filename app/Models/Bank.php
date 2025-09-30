<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class Bank extends Model
{
    protected $fillable = [
        'name',
        'code',
        'api_endpoint',
        'merchant_id',
        'api_key',
        'api_secret',
        'commission_rate',
        'is_active',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'commission_rate' => 'decimal:4',
            'is_active' => 'boolean',
            'settings' => 'array',
        ];
    }

    /**
     * Get encrypted API key
     */
    public function getApiKeyAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted API key
     */
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Get encrypted API secret
     */
    public function getApiSecretAttribute($value)
    {
        return $value ? Crypt::decryptString($value) : null;
    }

    /**
     * Set encrypted API secret
     */
    public function setApiSecretAttribute($value)
    {
        $this->attributes['api_secret'] = $value ? Crypt::encryptString($value) : null;
    }

    /**
     * Calculate commission for amount
     */
    public function calculateCommission(float $amount): float
    {
        return $amount * $this->commission_rate;
    }

    /**
     * Scope for active banks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
