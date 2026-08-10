<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProofTest extends TestCase
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

    private function createEligibleClaim(User $user, Identity $identity, string $claimType = 'income_threshold', array $payload = []): Claim
    {
        if (empty($payload)) {
            $payload = [
                'monthly_income' => 5000000,
            ];
        }

        return Claim::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => $claimType,
            'status' => 'eligible',
            'payload' => $payload,
            'submitted_at' => now(),
        ]);
    }

    public function test_guest_cannot_generate_proof(): void
    {
        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_generate_proof_for_other_user_claim(): void
    {
        $owner = $this->createUser('owner@example.com');
        $ownerIdentity = $this->createIdentity($owner);
        $ownerClaim = $this->createEligibleClaim($owner, $ownerIdentity);

        $other = $this->createUser('other@example.com');

        Sanctum::actingAs($other);

        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => $ownerClaim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim_id');
    }

    public function test_user_cannot_generate_proof_for_submitted_claim(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);

        $claim = Claim::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => 'income_threshold',
            'status' => 'submitted',
            'payload' => [
                'monthly_income' => 5000000,
            ],
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim_id');
    }

    public function test_user_cannot_generate_proof_for_ineligible_claim(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);

        $claim = Claim::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => 'income_threshold',
            'status' => 'ineligible',
            'payload' => [
                'monthly_income' => 4000000,
            ],
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim_id');
    }

    public function test_user_can_generate_proof_for_eligible_claim(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createEligibleClaim($user, $identity);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertCreated();
        $response->assertJsonPath('proof.claim_id', $claim->id);
        $response->assertJsonPath('proof.identity_id', $identity->id);
        $response->assertJsonPath('proof.status', 'generated');
        $response->assertJsonPath('proof.proof_payload.identity_commitment', $identity->identity_commitment);
        $response->assertJsonStructure([
            'message',
            'proof' => [
                'id',
                'claim_id',
                'identity_id',
                'proof_hash',
                'proof_payload',
                'status',
                'created_at',
            ],
        ]);

        $proofHash = $response->json('proof.proof_hash');

        $this->assertIsString($proofHash);
        $this->assertSame(64, strlen($proofHash));

        $this->assertDatabaseHas('proofs', [
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'identity_id' => $identity->id,
            'status' => 'generated',
        ]);

        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'status' => 'proof_generated',
        ]);
    }

    public function test_duplicate_proof_generation_is_rejected(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createEligibleClaim($user, $identity);

        Sanctum::actingAs($user);

        $this->postJson('/api/proof/generate', [
            'claim_id' => $claim->id,
        ])->assertCreated();

        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim_id');
    }

    public function test_user_cannot_generate_proof_when_identity_is_revoked(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createEligibleClaim($user, $identity);

        $identity->forceFill([
            'status' => 'revoked',
        ])->save();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('identity');
    }

    public function test_user_cannot_generate_proof_without_identity(): void
    {
        $user = $this->createUser();

        $claim = Claim::create([
            'user_id' => $user->id,
            'identity_id' => null,
            'claim_type' => 'income_threshold',
            'status' => 'eligible',
            'payload' => [
                'monthly_income' => 5000000,
            ],
            'submitted_at' => now(),
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('identity');
    }

    public function test_proof_response_does_not_expose_identity_secret(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createEligibleClaim($user, $identity);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/generate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertCreated();
        $response->assertJsonMissingPath('proof.identity_secret');
        $response->assertJsonMissingPath('proof.proof_payload.identity_secret');
    }
}
