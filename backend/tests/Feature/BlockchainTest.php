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

class BlockchainTest extends TestCase
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

    public function test_guest_cannot_store_blockchain_proof(): void
    {
        $response = $this->postJson('/api/blockchain/store', [
            'proof_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_store_other_user_proof(): void
    {
        $owner = $this->createUser('owner@example.com');
        $ownerIdentity = $this->createIdentity($owner);
        $ownerClaim = $this->createClaim($owner, $ownerIdentity);
        $ownerProof = $this->createProof($owner, $ownerIdentity, $ownerClaim);

        $other = $this->createUser('other@example.com');

        Sanctum::actingAs($other);

        $response = $this->postJson('/api/blockchain/store', [
            'proof_id' => $ownerProof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_proof_not_found_for_user(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/blockchain/store', [
            'proof_id' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_user_cannot_store_expired_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'expired');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/blockchain/store', [
            'proof_id' => $proof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_user_cannot_store_rejected_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'rejected');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/blockchain/store', [
            'proof_id' => $proof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_user_can_store_generated_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'generated');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/blockchain/store', [
            'proof_id' => $proof->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('stored', true);
        $response->assertJsonPath('blockchain_log.proof_id', $proof->id);
        $response->assertJsonPath('blockchain_log.network', 'ethereum');
        $response->assertJsonPath('blockchain_log.status', 'confirmed');
        $response->assertJsonPath('blockchain_log.event_name', 'ProofStored');
        $response->assertJsonPath('blockchain_log.payload.simulation', true);
        $response->assertJsonStructure([
            'message',
            'stored',
            'blockchain_log' => [
                'id',
                'proof_id',
                'network',
                'contract_address',
                'tx_hash',
                'block_number',
                'event_name',
                'payload',
                'status',
                'created_at',
            ],
        ]);

        $txHash = $response->json('blockchain_log.tx_hash');

        $this->assertStringStartsWith('0x', $txHash);
        $this->assertSame(66, strlen($txHash));
        $this->assertIsInt($response->json('blockchain_log.block_number'));

        $this->assertDatabaseHas('blockchain_logs', [
            'proof_id' => $proof->id,
            'network' => 'ethereum',
            'status' => 'confirmed',
        ]);
    }

    public function test_user_can_store_shared_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'shared');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/blockchain/store', [
            'proof_id' => $proof->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('stored', true);
        $response->assertJsonPath('blockchain_log.proof_id', $proof->id);
    }

    public function test_duplicate_blockchain_store_returns_existing_log(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'generated');

        Sanctum::actingAs($user);

        $first = $this->postJson('/api/blockchain/store', [
            'proof_id' => $proof->id,
        ]);

        $first->assertCreated();
        $first->assertJsonPath('stored', true);

        $logId = $first->json('blockchain_log.id');
        $txHash = $first->json('blockchain_log.tx_hash');

        $second = $this->postJson('/api/blockchain/store', [
            'proof_id' => $proof->id,
        ]);

        $second->assertOk();
        $second->assertJsonPath('stored', false);
        $second->assertJsonPath('blockchain_log.id', $logId);
        $second->assertJsonPath('blockchain_log.tx_hash', $txHash);

        $this->assertDatabaseCount('blockchain_logs', 1);
    }
}
