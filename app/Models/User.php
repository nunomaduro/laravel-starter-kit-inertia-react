<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\UserRole;
use Carbon\CarbonInterface;
use Database\Factories\UserFactory;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * @property-read string $id
 * @property-read string $name
 * @property-read string $email
 * @property-read string|null $phone
 * @property-read UserRole $role
 * @property-read string|null $avatar
 * @property-read string|null $bio
 * @property-read string|null $commission_rate
 * @property-read CarbonInterface|null $email_verified_at
 * @property-read string $password
 * @property-read string|null $remember_token
 * @property-read string|null $two_factor_secret
 * @property-read string|null $two_factor_recovery_codes
 * @property-read CarbonInterface|null $two_factor_confirmed_at
 * @property-read CarbonInterface $created_at
 * @property-read CarbonInterface $updated_at
 */
final class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<UserFactory> */
    use HasFactory;

    use HasUuids;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * @return array<string, string>
     */
    public function casts(): array
    {
        return [
            'id' => 'string',
            'name' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'role' => UserRole::class,
            'avatar' => 'string',
            'bio' => 'string',
            'commission_rate' => 'decimal:4',
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'remember_token' => 'string',
            'two_factor_secret' => 'string',
            'two_factor_recovery_codes' => 'string',
            'two_factor_confirmed_at' => 'datetime',
            'created_at' => 'datetime',
            'updated_at' => 'datetime',
        ];
    }

    /**
     * @return HasMany<Property, $this>
     */
    public function properties(): HasMany
    {
        return $this->hasMany(Property::class, 'host_id');
    }

    /**
     * @return HasMany<Booking, $this>
     */
    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class, 'guest_id');
    }

    /**
     * @return HasMany<Review, $this>
     */
    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class, 'guest_id');
    }

    /**
     * @return HasMany<Conversation, $this>
     */
    public function conversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'guest_id');
    }

    /**
     * @return HasMany<Conversation, $this>
     */
    public function hostedConversations(): HasMany
    {
        return $this->hasMany(Conversation::class, 'host_id');
    }

    /**
     * @return HasMany<Wishlist, $this>
     */
    public function wishlists(): HasMany
    {
        return $this->hasMany(Wishlist::class);
    }

    /**
     * @return HasMany<Message, $this>
     */
    public function sentMessages(): HasMany
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public function isHost(): bool
    {
        return $this->role === UserRole::Host;
    }

    public function isAdmin(): bool
    {
        return $this->role === UserRole::Admin;
    }

    public function isGuest(): bool
    {
        return $this->role === UserRole::Guest;
    }
}
