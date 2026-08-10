<?php

namespace App\Services;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class AuthService
{
    public function register(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'role' => 'user',
                'is_active' => true,
            ]);

            $this->generateOtp($user, 'register');

            return [
                'message' => 'Register success. Please verify your email.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'email_verified' => false,
                ],
            ];
        });
    }

    public function verifyEmail(array $data): array
    {
        return DB::transaction(function () use ($data) {
            $otp = OtpCode::where('email', $data['email'])
                ->where('purpose', 'register')
                ->whereNull('used_at')
                ->where('expires_at', '>', now())
                ->latest('id')
                ->first();

            if (! $otp || ! hash_equals($otp->code, hash('sha256', $data['code']))) {
                throw ValidationException::withMessages([
                    'code' => ['Invalid OTP code.'],
                ]);
            }

            $user = User::where('email', $data['email'])->firstOrFail();

            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();

            $otp->forceFill([
                'used_at' => now(),
            ])->save();

            return [
                'message' => 'Email verified.',
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'role' => $user->role,
                    'email_verified' => true,
                ],
            ];
        });
    }

    public function login(array $data): array
    {
        $user = User::where('email', $data['email'])->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        if (! $user->email_verified_at) {
            throw ValidationException::withMessages([
                'email' => ['Email not verified.'],
            ]);
        }

        if (! $user->is_active) {
            throw ValidationException::withMessages([
                'email' => ['Account is inactive.'],
            ]);
        }

        $user->forceFill([
            'last_login_at' => now(),
        ])->save();

        $token = $user->createToken('auth-token')->plainTextToken;

        return [
            'message' => 'Login success.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'email_verified' => true,
            ],
            'token' => $token,
        ];
    }

    private function generateOtp(User $user, string $purpose): void
    {
        OtpCode::where('email', $user->email)
            ->where('purpose', $purpose)
            ->update([
                'used_at' => now(),
            ]);

        $code = str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        OtpCode::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => hash('sha256', $code),
            'purpose' => $purpose,
            'expires_at' => now()->addMinutes(10),
        ]);

        Mail::to($user->email)->send(new OtpMail($code, $user->name));
    }
}
