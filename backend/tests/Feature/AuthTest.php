<?php

namespace Tests\Feature;

use App\Mail\OtpMail;
use App\Models\OtpCode;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_register(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('user.email', 'user@example.com');
        $response->assertJsonPath('user.email_verified', false);

        $this->assertDatabaseHas('users', [
            'email' => 'user@example.com',
            'role' => 'user',
        ]);

        $this->assertDatabaseHas('otp_codes', [
            'email' => 'user@example.com',
            'purpose' => 'register',
        ]);

        Mail::assertSent(OtpMail::class);
    }

    public function test_register_rejects_duplicate_email(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->postJson('/api/auth/register', $payload)->assertCreated();
        $this->postJson('/api/auth/register', $payload)->assertStatus(422);
    }

    public function test_user_can_verify_email_and_login(): void
    {
        Mail::fake();

        $payload = [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $this->postJson('/api/auth/register', $payload)->assertCreated();

        $code = null;

        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $verifyResponse = $this->postJson('/api/auth/verify-email', [
            'email' => 'user@example.com',
            'code' => $code,
        ]);

        $verifyResponse->assertOk();
        $verifyResponse->assertJsonPath('user.email_verified', true);

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $loginResponse->assertOk();
        $loginResponse->assertJsonStructure([
            'message',
            'user',
            'token',
        ]);
    }

    public function test_verify_email_rejects_invalid_code(): void
    {
        $user = User::create([
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true,
        ]);

        OtpCode::create([
            'user_id' => $user->id,
            'email' => $user->email,
            'code' => hash('sha256', '123456'),
            'purpose' => 'register',
            'expires_at' => now()->addMinutes(10),
        ]);

        $response = $this->postJson('/api/auth/verify-email', [
            'email' => 'user@example.com',
            'code' => '999999',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_when_email_not_verified(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
    }

    public function test_login_fails_with_wrong_password(): void
    {
        Mail::fake();

        $this->postJson('/api/auth/register', [
            'name' => 'Test User',
            'email' => 'user@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $code = null;

        Mail::assertSent(OtpMail::class, function (OtpMail $mail) use (&$code) {
            $code = $mail->code;

            return true;
        });

        $this->postJson('/api/auth/verify-email', [
            'email' => 'user@example.com',
            'code' => $code,
        ])->assertOk();

        $response = $this->postJson('/api/auth/login', [
            'email' => 'user@example.com',
            'password' => 'wrong-password',
        ]);

        $response->assertStatus(422);
    }
}
