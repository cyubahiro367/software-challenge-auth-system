<?php

namespace App\Services;

use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use App\Models\User;
use Illuminate\Http\Request;

class LoginService
{

  public function login(Request $request): array
  {
    $this->checkRateLimit($request);

    $user = User::where('email', $request->email)->first();

    if (! $user || ! Hash::check($request->password, $user->password)) {
      RateLimiter::hit($this->throttleKey($request), 60); // lock for 60 seconds
      throw ValidationException::withMessages([
        'email' => ['Invalid credentials. Please try again.'],
      ]);
    }

    RateLimiter::clear($this->throttleKey($request));

    $token = $user->createToken('auth_token')->plainTextToken;

    return [
      'token' => $token,
    ];
  }

  protected function checkRateLimit($data): void
  {
    if (RateLimiter::tooManyAttempts($this->throttleKey($data), 5)) {
      throw ValidationException::withMessages([
        'email' => ['Too many login attempts. Please try again later.'],
      ])->status(429);
    }
  }

  protected function throttleKey($data): string
  {
    return Str::lower($data->email) . '|' . $data->ip();
  }
}
