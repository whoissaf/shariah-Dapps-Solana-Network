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

class AiExplanationTest extends TestCase
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

    public function test_guest_cannot_request_ai_explanation(): void
    {
        $response = $this->postJson('/api/ai/explain', [
            'verification_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_regular_user_cannot_request_ai_explanation(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/ai/explain', [
            'verification_id' => 1,
        ]);

        $response->assertForbidden();
    }

    public function test_verifier_cannot_explain_nonexistent_verification(): void
    {
        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/ai/explain', [
            'verification_id' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('verification_id');
    }

    public function test_verifier_can_explain_passed_verification(): void
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

        $verificationId = $verifyResponse->json('verification.id');

        $response = $this->postJson('/api/ai/explain', [
            'verification_id' => $verificationId,
        ]);

        $response->assertOk();
        $response->assertJsonPath('explanation.recommendation', 'approve');
        $response->assertJsonPath('explanation.why_reject', []);
        $response->assertJsonPath('explanation.model', 'mvp-simulated-ai');
        $response->assertJsonStructure([
            'message',
            'explanation' => [
                'model',
                'verification_id',
                'verifier_id',
                'recommendation',
                'summary',
                'why_pass',
                'why_reject',
                'rule_violated',
                'generated_at',
            ],
        ]);

        $verification = Verification::find($verificationId);

        $this->assertNotNull($verification->ai_explanation);
        $this->assertSame('approve', $verification->ai_explanation['recommendation']);
    }

    public function test_verifier_can_explain_failed_verification(): void
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

        $verifyResponse = $this->postJson('/api/verification/verify', [
            'proof_id' => $proof->id,
        ]);

        $verifyResponse->assertOk();
        $verifyResponse->assertJsonPath('verification.technical_passed', false);

        $verificationId = $verifyResponse->json('verification.id');

        $response = $this->postJson('/api/ai/explain', [
            'verification_id' => $verificationId,
        ]);

        $response->assertOk();
        $response->assertJsonPath('explanation.recommendation', 'reject');
        $response->assertJsonPath('explanation.rule_violated.0.rule_code', 'income_threshold');

        $this->assertNotEmpty($response->json('explanation.why_reject'));
    }

    public function test_explanation_updates_same_verification(): void
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

        $verificationId = $verifyResponse->json('verification.id');

        $this->postJson('/api/ai/explain', [
            'verification_id' => $verificationId,
        ])->assertOk();

        $this->postJson('/api/ai/explain', [
            'verification_id' => $verificationId,
        ])->assertOk();

        $this->assertDatabaseCount('verifications', 1);

        $verification = Verification::find($verificationId);

        $this->assertNotNull($verification->ai_explanation);
    }

    public function test_verifier_cannot_explain_final_verification(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $verification = Verification::create([
            'proof_id' => $proof->id,
            'status' => 'verified',
            'verified_at' => now(),
        ]);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/ai/explain', [
            'verification_id' => $verification->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('verification_id');
    }
}
