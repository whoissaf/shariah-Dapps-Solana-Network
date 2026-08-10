<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\Proof;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class VerifierQrTest extends TestCase
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

    private function createWallet(User $user): Wallet
    {
        return Wallet::create([
            'user_id' => $user->id,
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
            'wallet_session' => 'wallet-session',
            'connected_at' => now(),
            'status' => 'connected',
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

    private function createProof(User $user, Identity $identity, Claim $claim, string $status = 'generated'): Proof
    {
        return Proof::create([
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'identity_id' => $identity->id,
            'proof_hash' => hash('sha256', Str::random(20)),
            'proof_payload' => [
                'circuit' => 'mvp-zk-simulation',
            ],
            'status' => $status,
        ]);
    }

    private function shareProof(Proof $proof): array
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

        return [
            'proof_id' => $proof->id,
            'nonce' => $nonce,
            'signature' => $signature,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    private function shareExpiredProof(Proof $proof): array
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

        return [
            'proof_id' => $proof->id,
            'nonce' => $nonce,
            'signature' => $signature,
            'expires_at' => $expiresAt->toIso8601String(),
        ];
    }

    public function test_guest_cannot_read_qr(): void
    {
        $response = $this->postJson('/api/verification/read', [
            'proof_id' => 1,
            'nonce' => str_repeat('a', 32),
            'signature' => str_repeat('b', 64),
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);

        $response->assertUnauthorized();
    }

    public function test_regular_user_cannot_read_qr(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/verification/read', [
            'proof_id' => 1,
            'nonce' => str_repeat('a', 32),
            'signature' => str_repeat('b', 64),
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);

        $response->assertForbidden();
    }

    public function test_verifier_can_read_valid_qr(): void
    {
        $user = $this->createUser();
        $wallet = $this->createWallet($user);
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $qr = $this->shareProof($proof);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/read', $qr);

        $response->assertOk();
        $response->assertJsonPath('proof.proof_id', $proof->id);
        $response->assertJsonPath('proof.status', 'shared');
        $response->assertJsonPath('proof.claim.claim_type', 'income_threshold');
        $response->assertJsonPath('proof.identity.identity_commitment', $identity->identity_commitment);
        $response->assertJsonPath('proof.wallet.wallet_address', $wallet->wallet_address);
        $response->assertJsonMissingPath('proof.identity.identity_secret');
    }

    public function test_verifier_cannot_read_qr_with_invalid_signature(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $qr = $this->shareProof($proof);
        $qr['signature'] = str_repeat('c', 64);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/read', $qr);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('qr');
    }

    public function test_verifier_cannot_read_qr_with_wrong_nonce(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $qr = $this->shareProof($proof);
        $qr['nonce'] = str_repeat('d', 32);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/read', $qr);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('qr');
    }

    public function test_verifier_cannot_read_expired_qr(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $qr = $this->shareExpiredProof($proof);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/read', $qr);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('qr');
    }

    public function test_verifier_cannot_read_proof_without_qr(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/read', [
            'proof_id' => $proof->id,
            'nonce' => str_repeat('e', 32),
            'signature' => str_repeat('f', 64),
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('qr');
    }

    public function test_verifier_cannot_read_nonexistent_proof(): void
    {
        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->postJson('/api/verification/read', [
            'proof_id' => 999999,
            'nonce' => str_repeat('a', 32),
            'signature' => str_repeat('b', 64),
            'expires_at' => now()->addMinutes(10)->toIso8601String(),
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_guest_cannot_fetch_proof(): void
    {
        $response = $this->getJson('/api/proof/1');

        $response->assertUnauthorized();
    }

    public function test_regular_user_cannot_fetch_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareProof($proof);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/proof/' . $proof->id);

        $response->assertForbidden();
    }

    public function test_verifier_can_fetch_shared_proof(): void
    {
        $user = $this->createUser();
        $wallet = $this->createWallet($user);
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim);

        $this->shareProof($proof);

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/proof/' . $proof->id);

        $response->assertOk();
        $response->assertJsonPath('proof.proof_id', $proof->id);
        $response->assertJsonPath('proof.status', 'shared');
        $response->assertJsonPath('proof.claim.claim_type', 'income_threshold');
        $response->assertJsonPath('proof.identity.identity_commitment', $identity->identity_commitment);
        $response->assertJsonPath('proof.wallet.wallet_address', $wallet->wallet_address);
        $response->assertJsonMissingPath('proof.identity.identity_secret');
    }

    public function test_verifier_cannot_fetch_generated_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'generated');

        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/proof/' . $proof->id);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_verifier_cannot_fetch_nonexistent_proof(): void
    {
        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/proof/999999');

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }
}
