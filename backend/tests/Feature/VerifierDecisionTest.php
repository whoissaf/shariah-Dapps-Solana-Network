<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\Proof;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VerifierDecisionTest extends TestCase
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
            'status' => 'eligible',
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
                'identity_commitment' => $identity->identity_commitment,
            ],
            'status' => 'generated',
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

    private function preparePendingVerification(): array
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareProof($proof);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $verifyResponse = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $verifyResponse->assertOk();

        return [
            $verifier,
            $proof,
            $verifyResponse->json('verification.id'),
        ];
    }

    public function test_guest_cannot_approve_verification(): void
    {
        $response = $this->postJson('/api/verification/approve', [
            'verification_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_reject_verification(): void
    {
        $response = $this->postJson('/api/verification/reject', [
            'verification_id' => 1,
            'reason' => 'Invalid document.',
        ]);

        $response->assertUnauthorized();
    }

    public function test_regular_user_cannot_approve_verification(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/verification/approve', [
            'verification_id' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_regular_user_cannot_reject_verification(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/verification/reject', [
            'verification_id' => 1,
            'reason' => 'Invalid document.',
        ]);

        $response->assertForbidden();
    }

    public function test_verifier_cannot_approve_nonexistent_verification(): void
    {
        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/approve', [
            'verification_id' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('verification_id');
    }

    public function test_verifier_can_approve_pending_verification(): void
    {
        [$verifier, $proof, $verificationId] = $this->preparePendingVerification();

        $response = $this->postJson('/api/verification/approve', [
            'verification_id' => $verificationId,
        ]);

        $response->assertOk();
        $response->assertJsonPath('verification.status', 'verified');
        $response->assertJsonPath('proof.status', 'verified');
        $response->assertJsonPath('verification.reject_reason', null);

        $this->assertDatabaseHas('verifications', [
            'id' => $verificationId,
            'proof_id' => $proof->id,
            'status' => 'verified',
        ]);

        $this->assertDatabaseHas('proofs', [
            'id' => $proof->id,
            'status' => 'verified',
        ]);
    }

    public function test_verifier_can_reject_pending_verification(): void
    {
        [$verifier, $proof, $verificationId] = $this->preparePendingVerification();

        $reason = 'Income evidence does not meet threshold.';

        $response = $this->postJson('/api/verification/reject', [
            'verification_id' => $verificationId,
            'reason' => $reason,
        ]);

        $response->assertOk();
        $response->assertJsonPath('verification.status', 'rejected');
        $response->assertJsonPath('verification.reject_reason', $reason);
        $response->assertJsonPath('proof.status', 'rejected');

        $this->assertDatabaseHas('verifications', [
            'id' => $verificationId,
            'proof_id' => $proof->id,
            'status' => 'rejected',
            'reject_reason' => $reason,
        ]);

        $this->assertDatabaseHas('proofs', [
            'id' => $proof->id,
            'status' => 'rejected',
        ]);
    }

    public function test_reject_requires_reason(): void
    {
        [$verifier, $proof, $verificationId] = $this->preparePendingVerification();

        $response = $this->postJson('/api/verification/reject', [
            'verification_id' => $verificationId,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('reason');
    }

    public function test_reject_reason_must_be_at_least_five_characters(): void
    {
        [$verifier, $proof, $verificationId] = $this->preparePendingVerification();

        $response = $this->postJson('/api/verification/reject', [
            'verification_id' => $verificationId,
            'reason' => 'abc',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('reason');
    }

    public function test_verifier_cannot_approve_already_approved_verification(): void
    {
        [$verifier, $proof, $verificationId] = $this->preparePendingVerification();

        $this->postJson('/api/verification/approve', [
            'verification_id' => $verificationId,
        ])->assertOk();

        $response = $this->postJson('/api/verification/approve', [
            'verification_id' => $verificationId,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('verification_id');
    }

    public function test_verifier_cannot_reject_already_rejected_verification(): void
    {
        [$verifier, $proof, $verificationId] = $this->preparePendingVerification();

        $this->postJson('/api/verification/reject', [
            'verification_id' => $verificationId,
            'reason' => 'Income evidence does not meet threshold.',
        ])->assertOk();

        $response = $this->postJson('/api/verification/reject', [
            'verification_id' => $verificationId,
            'reason' => 'Income evidence does not meet threshold.',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('verification_id');
    }

    public function test_verifier_cannot_approve_after_rejection(): void
    {
        [$verifier, $proof, $verificationId] = $this->preparePendingVerification();

        $this->postJson('/api/verification/reject', [
            'verification_id' => $verificationId,
            'reason' => 'Income evidence does not meet threshold.',
        ])->assertOk();

        $response = $this->postJson('/api/verification/approve', [
            'verification_id' => $verificationId,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('verification_id');
    }

    public function test_approve_does_not_create_new_verification(): void
    {
        [$verifier, $proof, $verificationId] = $this->preparePendingVerification();

        $this->postJson('/api/verification/approve', [
            'verification_id' => $verificationId,
        ])->assertOk();

        $this->assertDatabaseCount('verifications', 1);
    }
}
