<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Str;
use Laravel\Fortify\TwoFactorAuthenticatable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable, TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'phone',
        'role',
        'unique_id',
        'two_factor_enabled',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'is_active',
        'last_login_at',
        'balance_rub',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'two_factor_enabled' => 'boolean',
            'two_factor_recovery_codes' => 'array',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'balance_rub' => 'decimal:2',
        ];
    }

    /**
     * Get the user's initials
     */
    public function initials(): string
    {
        return Str::of($this->name)
            ->explode(' ')
            ->take(2)
            ->map(fn ($word) => Str::substr($word, 0, 1))
            ->implode('');
    }

    /**
     * Check if user has specific role
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Check if user is admin
     */
    public function isAdmin(): bool
    {
        return $this->hasRole('admin');
    }

    /**
     * Check if user is broker
     */
    public function isBroker(): bool
    {
        return $this->hasRole('broker');
    }

    /**
     * Check if user is client
     */
    public function isClient(): bool
    {
        return $this->hasRole('client');
    }

    /**
     * Generate unique ID for user
     */
    public static function generateUniqueId(): string
    {
        do {
            $uniqueId = Str::random(10);
        } while (static::where('unique_id', $uniqueId)->exists());

        return $uniqueId;
    }

    /**
     * Boot method to generate unique ID
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($user) {
            if (empty($user->unique_id)) {
                $user->unique_id = static::generateUniqueId();
            }
        });
    }

    /**
     * Get user transactions
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get user balances
     */
    public function balances()
    {
        return $this->hasMany(UserBalance::class);
    }

    /**
     * Get user winner/loser records
     */
    public function winnerLosers()
    {
        return $this->hasMany(WinnerLoser::class);
    }

    /**
     * Get user cards
     */
    public function cards()
    {
        return $this->hasMany(UserCard::class);
    }

    /**
     * Get active user cards
     */
    public function activeCards()
    {
        return $this->hasMany(UserCard::class)->where('is_active', true);
    }

    /**
     * Get default card
     */
    public function defaultCard()
    {
        return $this->hasOne(UserCard::class)->where('is_default', true);
    }

    /**
     * |KB Группы пользователя (для правил обработки платежей в банке)
     */
    public function userGroups()
    {
        return $this->belongsToMany(UserGroup::class, 'user_group_user')
            ->withPivot(['assigned_by', 'comment'])
            ->withTimestamps();
    }

    /**
     * Add to RUB balance
     */
    public function addRubBalance(float $amount): bool
    {
        $this->balance_rub += $amount;
        return $this->save();
    }

    /**
     * Subtract from RUB balance
     */
    public function subtractRubBalance(float $amount): bool
    {
        if ($this->balance_rub < $amount) {
            return false;
        }

        $this->balance_rub -= $amount;
        return $this->save();
    }

    /**
     * Check if user has enough RUB balance
     */
    public function hasEnoughRubBalance(float $amount): bool
    {
        return $this->balance_rub >= $amount;
    }

    /**
     * Get formatted RUB balance
     */
    public function getFormattedRubBalanceAttribute(): string
    {
        return number_format($this->balance_rub, 2, '.', ' ') . ' ₽';
    }
}
