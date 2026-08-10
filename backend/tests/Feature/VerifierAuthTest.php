<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\Proof;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VerifierAuthTest extends TestCase
{
    use RefreshDatabase;

    private function createUser(string $email = 'user@example.com'): User
    {
        return User::create([
            'name' => 'Test User',
            'email' => $email,
            'password' => 'password123',
            'role' => 'user',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function createVerifier(string $email = 'verifier@example.com'): User
    {
        return User::create([
            'name' => 'Verifier User',
            'email' => $email,
            'password' => 'password123',
            'role' => 'verifier',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }

    private function createIdentity(User $user): Identity
    {
        $anonymousId = (string) Str::uuid();
        $secret = bin2hex(random_bytes(32));

        return Identity::create([
            'user_id' => $user->id,
            'anonymous_id' => $anonymousId,
            'identity_secret' => $secret,
            'identity_commitment' => hash('sha256', $anonymousId . ':' . $secret),
            'status' => 'active',
        ]);
    }

    private function createClaim(User $user, Identity $identity): Claim
    {
        return Claim::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => 'income_threshold',
            'status' => 'proof_generated',
            'payload' => [
                'monthly_income' => 5000000,
            ],
            'submitted_at' => now(),
        ]);
    }

    private function createProof(User $user, Identity $identity, Claim $claim): Proof
    {
        return Proof::create([
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'identity_id' => $identity->id,
            'proof_hash' => hash('sha256', Str::random(20)),
            'proof_payload' => [
                'circuit' => 'mvp-zk-simulation',
            ],
            'status' => 'generated',
        ]);
    }

    private function createVerification(Proof $proof, string $status): Verification
    {
        return Verification::create([
            'proof_id' => $proof->id,
            'status' => $status,
            'verified_at' => in_array($status, ['verified', 'rejected']) ? now() : null,
        ]);
    }

    public function test_guest_cannot_access_verifier_dashboard(): void
    {
        $response = $this->getJson('/api/dashboard');

        $response->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_verifier_dashboard(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/dashboard');

        $response->assertForbidden();
    }

    public function test_verifier_cannot_access_user_profile(): void
    {
        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/profile');

        $response->assertForbidden();
    }

    public function test_verifier_can_access_dashboard_with_empty_state(): void
    {
        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonPath('dashboard.pending', 0);
        $response->assertJsonPath('dashboard.verified', 0);
        $response->assertJsonPath('dashboard.rejected', 0);
        $response->assertJsonPath('dashboard.today_total', 0);
        $response->assertJsonStructure([
            'message',
            'dashboard' => [
                'pending',
                'verified',
                'rejected',
                'today_total',
            ],
            'recent',
        ]);
    }

    public function test_verifier_login_returns_token_and_can_access_dashboard(): void
    {
        $verifier = $this->createVerifier('verifier.login@example.com');

        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => $verifier->email,
            'password' => 'password123',
        ]);

        $loginResponse->assertOk();
        $loginResponse->assertJsonPath('user.role', 'verifier');
        $loginResponse->assertJsonStructure([
            'message',
            'user',
            'token',
        ]);

        $token = $loginResponse->json('token');

        $dashboardResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/dashboard');

        $dashboardResponse->assertOk();
    }

    public function test_unverified_verifier_cannot_login(): void
    {
        $verifier = $this->createVerifier('unverified.verifier@example.com');

        $verifier->forceFill([
            'email_verified_at' => null,
        ])->save();

        $response = $this->postJson('/api/auth/login', [
            'email' => $verifier->email,
            'password' => 'password123',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('email');
    }

    public function test_register_cannot_create_verifier_role(): void
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'name' => 'Attempt Verifier',
            'email' => 'attempt.verifier@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
            'role' => 'verifier',
        ]);

        $response->assertCreated();

        $this->assertDatabaseHas('users', [
            'email' => 'attempt.verifier@example.com',
            'role' => 'user',
        ]);
    }

    public function test_verifier_dashboard_counts_verification_statuses(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->createVerification($proof, 'pending');
        $this->createVerification($proof, 'verified');
        $this->createVerification($proof, 'rejected');

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/dashboard');

        $response->assertOk();
        $response->assertJsonPath('dashboard.pending', 1);
        $response->assertJsonPath('dashboard.verified', 1);
        $response->assertJsonPath('dashboard.rejected', 1);
        $response->assertJsonPath('dashboard.today_total', 3);
    }
}
