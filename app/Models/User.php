<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'honorific_title',
        'first_name',
        'last_name',
        'gender',
        'date_of_birth',
        'email',
        'phone_number',
        'nationality',
        'profile_picture',
        'country_of_residence',
        'city',
        'postal_code',
        'apartment_name',
        'room_number',
        'is_expatriate',
        'password',
        'two_factor_verified',
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'date_of_birth' => 'date',
        'two_factor_expires_at' => 'datetime',
        'locked_until' => 'datetime',
        'is_expatriate' => 'boolean',
        'two_factor_verified' => 'boolean',
    ];

    // Automatically set honorific title based on gender
    public function setGenderAttribute($value)
    {
        $this->attributes['gender'] = $value;

        // Auto-set honorific title if not provided
        if (empty($this->attributes['honorific_title'])) {
            $this->attributes['honorific_title'] = $value === 'male' ? 'Mr.' : 'Ms.';
        }
    }

    // Check if account is locked due to failed login attempts
    public function isLocked()
    {
        return $this->locked_until && $this->locked_until->isFuture();
    }

    // Increment failed login attempts
    public function incrementFailedAttempts()
    {
        $this->increment('failed_login_attempts');

        // Lock account after 5 failed attempts for 15 minutes
        if ($this->failed_login_attempts >= 5) {
            $this->update(['locked_until' => Carbon::now()->addMinutes(15)]);
        }
    }

    // Reset failed login attempts
    public function resetFailedAttempts()
    {
        $this->update([
            'failed_login_attempts' => 0,
            'locked_until' => null,
        ]);
    }

    // Generate and set 2FA code
    public function generateTwoFactorCode()
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        $this->update([
            'two_factor_code' => Hash::make($code),
            'two_factor_expires_at' => Carbon::now()->addMinutes(10), // Expires in 10 minutes
        ]);

        return $code; // Return plain code for sending via email
    }

    // Verify 2FA code
    public function verifyTwoFactorCode($code)
    {
        if (! $this->two_factor_code || Carbon::now()->isAfter($this->two_factor_expires_at)) {
            return false;
        }

        if (Hash::check($code, $this->two_factor_code)) {
            $this->update([
                'two_factor_code' => null,
                'two_factor_expires_at' => null,
                'two_factor_verified' => true,
            ]);

            return true;
        }

        return false;
    }

    // Check if user is expatriate
    public function setCountryOfResidenceAttribute($value)
    {
        $this->attributes['country_of_residence'] = $value;
        $this->attributes['is_expatriate'] = $value !== $this->nationality;
    }

    // Get full name
    public function getFullNameAttribute()
    {
        return "{$this->honorific_title} {$this->first_name} {$this->last_name}";
    }
}
