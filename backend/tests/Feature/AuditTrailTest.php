<?php

namespace Tests\Feature;

use App\Models\BlockchainLog;
use App\Models\Claim;
use App\Models\Identity;
use App\Models\Proof;
use App\Models\User;
use App\Models\Verification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class AuditTrailTest extends TestCase
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

    private function createProof(User $user, Identity $identity, Claim $claim, string $status = 'shared'): Proof
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

    private function createBlockchainLog(Proof $proof): BlockchainLog
    {
        return BlockchainLog::create([
            'proof_id' => $proof->id,
            'network' => 'ethereum',
            'contract_address' => '0x5fbdb2315678afecb367f032d93f642f64180aa3',
            'tx_hash' => '0x' . hash('sha256', Str::random(20)),
            'block_number' => random_int(100000, 9999999),
            'event_name' => 'ProofStored',
            'payload' => [
                'proof_hash' => $proof->proof_hash,
                'simulation' => true,
            ],
            'status' => 'confirmed',
        ]);
    }

    private function createVerification(Proof $proof, User $verifier, string $status = 'verified', ?string $rejectReason = null): Verification
    {
        return Verification::create([
            'proof_id' => $proof->id,
            'verifier_id' => $verifier->id,
            'status' => $status,
            'reject_reason' => $rejectReason,
            'verified_at' => in_array($status, ['verified', 'rejected']) ? now() : null,
        ]);
    }

    public function test_guest_cannot_access_audit(): void
    {
        $response = $this->getJson('/api/audit');

        $response->assertUnauthorized();
    }

    public function test_regular_user_cannot_access_audit(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/audit');

        $response->assertForbidden();
    }

    public function test_verifier_can_access_empty_audit(): void
    {
        $verifier = $this->createVerifier();

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/audit');

        $response->assertOk();
        $response->assertJsonCount(0, 'audit');
    }

    public function test_audit_includes_verified_verification_with_blockchain_transaction(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'verified');

        $blockchainLog = $this->createBlockchainLog($proof);

        $verifier = $this->createVerifier();
        $verification = $this->createVerification($proof, $verifier, 'verified');

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/audit');

        $response->assertOk();
        $response->assertJsonCount(1, 'audit');
        $response->assertJsonPath('audit.0.verification_id', $verification->id);
        $response->assertJsonPath('audit.0.proof_id', $proof->id);
        $response->assertJsonPath('audit.0.claim_type', 'income_threshold');
        $response->assertJsonPath('audit.0.verification_status', 'verified');
        $response->assertJsonPath('audit.0.verifier.email', $verifier->email);
        $response->assertJsonPath('audit.0.ethereum_tx.tx_hash', $blockchainLog->tx_hash);
        $response->assertJsonPath('audit.0.ethereum_tx.network', 'ethereum');
        $response->assertJsonPath('audit.0.ethereum_tx.status', 'confirmed');
    }

    public function test_audit_without_blockchain_transaction_returns_null_ethereum_tx(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'verified');

        $verifier = $this->createVerifier();
        $this->createVerification($proof, $verifier, 'verified');

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/audit');

        $response->assertOk();
        $response->assertJsonCount(1, 'audit');

        $this->assertNull($response->json('audit.0.ethereum_tx'));
    }

    public function test_audit_includes_rejected_verification_with_reason(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'rejected');

        $verifier = $this->createVerifier();
        $reason = 'Income evidence does not meet threshold.';
        $this->createVerification($proof, $verifier, 'rejected', $reason);

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/audit');

        $response->assertOk();
        $response->assertJsonPath('audit.0.verification_status', 'rejected');
        $response->assertJsonPath('audit.0.reject_reason', $reason);
    }

    public function test_audit_lists_latest_verification_first(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);

        $firstProof = $this->createProof($user, $identity, $claim, 'verified');
        $secondProof = $this->createProof($user, $identity, $claim, 'verified');

        $verifier = $this->createVerifier();

        $firstVerification = $this->createVerification($firstProof, $verifier, 'verified');
        $secondVerification = $this->createVerification($secondProof, $verifier, 'verified');

        Sanctum::actingAs($verifier);

        $response = $this->getJson('/api/audit');

        $response->assertOk();
        $response->assertJsonCount(2, 'audit');
        $response->assertJsonPath('audit.0.verification_id', $secondVerification->id);
        $response->assertJsonPath('audit.1.verification_id', $firstVerification->id);
    }
}
