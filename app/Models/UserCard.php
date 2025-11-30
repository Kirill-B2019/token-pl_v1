<?php
// |KB Модель привязанной карты пользователя

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserCard extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id',
        'twocan_card_token',
        'card_mask',
        'card_brand',
        'card_holder_name',
        'expiry_month',
        'expiry_year',
        'is_active',
        'is_default',
    ];

    protected $hidden = [
        'twocan_card_token',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    /**
     * Get user that owns this card
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get masked card number for display
     */
    public function getMaskedNumberAttribute(): string
    {
        return $this->card_mask;
    }

    /**
     * Get formatted expiry date
     */
    public function getFormattedExpiryAttribute(): string
    {
        return sprintf('%02d/%02d', $this->expiry_month, $this->expiry_year);
    }

    /**
     * Check if card is expired
     */
    public function isExpired(): bool
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');

        if ($this->expiry_year > $currentYear) {
            return false;
        }

        if ($this->expiry_year === $currentYear && $this->expiry_month >= $currentMonth) {
            return false;
        }

        return true;
    }

    /**
     * Set this card as default for user
     */
    public function setAsDefault(): bool
    {
        // Remove default flag from other user's cards
        static::where('user_id', $this->user_id)->update(['is_default' => false]);

        // Set this card as default
        $this->is_default = true;
        return $this->save();
    }

    /**
     * Get default card for user
     */
    public static function getDefaultForUser(int $userId): ?static
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->where('is_default', true)
            ->first();
    }

    /**
     * Get active cards for user
     */
    public static function getActiveForUser(int $userId): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('user_id', $userId)
            ->where('is_active', true)
            ->orderBy('is_default', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();
    }
}