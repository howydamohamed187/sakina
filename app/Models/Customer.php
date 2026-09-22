<?php

namespace App\Models;

use App\Rules\TripleName;
use App\Support\Roles;
use Database\Factories\CustomerFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class Customer extends Authenticatable
{
    /** @use HasFactory<CustomerFactory> */
    use HasApiTokens, HasFactory, HasRoles, Notifiable, SoftDeletes;

    protected string $guard_name = 'web';

    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'location',
        'latitude',
        'longitude',
        'locale',
        'status',
        'email_verified_at',
        'sort_order',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'latitude' => 'float',
            'longitude' => 'float',
            'sort_order' => 'integer',
        ];
    }

    public function verificationCodes(): HasMany
    {
        return $this->hasMany(VerificationCode::class);
    }

    public function dailyQuestionAssignments(): HasMany
    {
        return $this->hasMany(DailyQuestionAssignment::class);
    }

    public function dailyQuestionAnswers(): HasMany
    {
        return $this->hasMany(DailyQuestionAnswer::class);
    }

    public function hadithFavorites(): HasMany
    {
        return $this->hasMany(HadithFavorite::class);
    }

    public function favoriteHadiths(): BelongsToMany
    {
        return $this->belongsToMany(Hadith::class, 'hadith_favorites')
            ->withTimestamps();
    }

    public function dhikrFavorites(): HasMany
    {
        return $this->hasMany(DhikrFavorite::class);
    }

    public function favoriteDhikrs(): BelongsToMany
    {
        return $this->belongsToMany(Dhikr::class, 'dhikr_favorites')
            ->withTimestamps();
    }

    public function duaFavorites(): HasMany
    {
        return $this->hasMany(DuaFavorite::class);
    }

    public function favoriteDuas(): BelongsToMany
    {
        return $this->belongsToMany(Dua::class, 'dua_favorites')
            ->withTimestamps();
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function needsActivation(): bool
    {
        return $this->email_verified_at === null || $this->status === 'pending';
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }

    public function isCustomer(): bool
    {
        return $this->hasRole(Roles::CUSTOMER);
    }

    public function avatarUrl(): ?string
    {
        if (! $this->avatar) {
            return null;
        }

        return Storage::disk('public')->url($this->avatar);
    }

    protected static function booted(): void
    {
        static::saving(function (Customer $customer): void {
            if (filled($customer->name)) {
                $customer->name = TripleName::normalize($customer->name);
            }
        });

        static::created(function (Customer $customer): void {
            $customer->assignRole(Roles::ensure(Roles::CUSTOMER));
        });

        static::forceDeleting(function (Customer $customer): void {
            $customer->tokens()->delete();
            $customer->notifications()->delete();
            $customer->hadithFavorites()->delete();
            $customer->dhikrFavorites()->delete();
            $customer->duaFavorites()->delete();
            $customer->roles()->detach();

            if ($customer->avatar) {
                Storage::disk('public')->delete($customer->avatar);
            }
        });
    }
}
