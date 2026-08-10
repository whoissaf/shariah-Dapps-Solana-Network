<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\Proof;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VerifierVerifyTest extends TestCase
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

    private function createClaim(User $user, Identity $identity, array $payload = [], string $status = 'eligible'): Claim
    {
        if (empty($payload)) {
            $payload = [
                'monthly_income' => 5000000,
            ];
        }

        return Claim::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => 'income_threshold',
            'status' => $status,
            'payload' => $payload,
            'submitted_at' => now(),
        ]);
    }

    private function createProof(User $user, Identity $identity, Claim $claim, string $status = 'generated'): Proof
    {
        return Proof::create([
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'identity_id' => $identity->id,
            'proof_hash' => hash('sha256', Str::random(20)),
            'proof_payload' => [
                'circuit' => 'mvp-zk-simulation',
                'identity_commitment' => $identity->identity_commitment,
            ],
            'status' => $status,
        ]);
    }

    private function shareProof(Proof $proof): void
    {
        $nonce = bin2hex(random_bytes(16));
        $expiresAt = now()->addMinutes(10);

        $signature = hash_hmac('sha256', implode(':', [
            $proof->id,
            $nonce,
            $proof->proof_hash,
            $expiresAt->toIso8601String(),
        ]), (string) config('app.key'));

        $proof->forceFill([
            'qr_nonce' => $nonce,
            'qr_signature' => $signature,
            'qr_expires_at' => $expiresAt,
            'status' => 'shared',
        ])->save();
    }

    private function shareExpiredProof(Proof $proof): void
    {
        $nonce = bin2hex(random_bytes(16));
        $expiresAt = now()->subMinutes(1);

        $signature = hash_hmac('sha256', implode(':', [
            $proof->id,
            $nonce,
            $proof->proof_hash,
            $expiresAt->toIso8601String(),
        ]), (string) config('app.key'));

        $proof->forceFill([
            'qr_nonce' => $nonce,
            'qr_signature' => $signature,
            'qr_expires_at' => $expiresAt,
            'status' => 'shared',
        ])->save();
    }

    public function test_guest_cannot_verify_proof(): void
    {
        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_regular_user_cannot_verify_proof(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_verifier_can_verify_valid_shared_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareProof($proof);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('verification.proof_id', $proof->id);
        $response->assertJsonPath('verification.status', 'pending');
        $response->assertJsonPath('verification.technical_passed', true);

        $this->assertDatabaseHas('verifications', [
            'proof_id' => $proof->id,
            'verifier_id' => $verifier->id,
            'status' => 'pending',
        ]);
    }

    public function test_verifier_cannot_verify_generated_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'generated');

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_verifier_cannot_verify_nonexistent_proof(): void
    {
        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_verify_returns_technical_failure_when_qr_expired(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareExpiredProof($proof);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('verification.technical_passed', false);

        $this->assertDatabaseHas('verifications', [
            'proof_id' => $proof->id,
            'status' => 'pending',
        ]);
    }

    public function test_verify_returns_technical_failure_when_identity_revoked(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareProof($proof);

        $identity->forceFill([
            'status' => 'revoked',
        ])->save();

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('verification.technical_passed', false);
    }

    public function test_verify_returns_rule_failure_when_claim_payload_fails_rule(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, [
            'monthly_income' => 4000000,
        ], 'eligible');
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareProof($proof);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('verification.technical_passed', false);
    }

    public function test_duplicate_verify_updates_existing_pending_verification(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareProof($proof);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $first = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $first->assertOk();

        $verificationId = $first->json('verification.id');

        $second = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $second->assertOk();
        $second->assertJsonPath('verification.id', $verificationId);

        $this->assertDatabaseCount('verifications', 1);
    }

    public function test_verifier_cannot_verify_proof_with_final_decision(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareProof($proof);

        Verification::create([
            'proof_id' => $proof->id,
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }
}
