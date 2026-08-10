<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Identity;
use App\Models\Proof;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ProofShareTest extends TestCase
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

    public function test_guest_cannot_share_proof(): void
    {
        $response = $this->postJson('/api/proof/share', [
            'proof_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_share_other_user_proof(): void
    {
        $owner = $this->createUser('owner@example.com');
        $ownerIdentity = $this->createIdentity($owner);
        $ownerClaim = $this->createClaim($owner, $ownerIdentity);
        $ownerProof = $this->createProof($owner, $ownerIdentity, $ownerClaim);

        $other = $this->createUser('other@example.com');

        Sanctum::actingAs($other);

        $response = $this->postJson('/api/proof/share', [
            'proof_id' => $ownerProof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_proof_not_found_for_user(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/share', [
            'proof_id' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_user_cannot_share_expired_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'expired');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/share', [
            'proof_id' => $proof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_user_cannot_share_rejected_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'rejected');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/share', [
            'proof_id' => $proof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_user_cannot_share_verified_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'verified');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/share', [
            'proof_id' => $proof->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('proof_id');
    }

    public function test_user_can_share_generated_proof(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'generated');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/proof/share', [
            'proof_id' => $proof->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('qr.proof_id', $proof->id);
        $response->assertJsonPath('proof.status', 'shared');
        $response->assertJsonStructure([
            'message',
            'qr' => [
                'proof_id',
                'qr_nonce',
                'qr_signature',
                'qr_expires_at',
                'expires_in_seconds',
                'qr_content',
            ],
            'proof' => [
                'id',
                'status',
            ],
        ]);

        $nonce = $response->json('qr.qr_nonce');
        $signature = $response->json('qr.qr_signature');
        $expiresAt = Carbon::parse($response->json('qr.qr_expires_at'));

        $this->assertSame(32, strlen($nonce));
        $this->assertSame(64, strlen($signature));
        $this->assertTrue($expiresAt->greaterThan(now()));
        $this->assertGreaterThan(0, $response->json('qr.expires_in_seconds'));

        $decodedQrContent = json_decode($response->json('qr.qr_content'), true);

        $this->assertIsArray($decodedQrContent);
        $this->assertSame($proof->id, $decodedQrContent['proof_id']);
        $this->assertSame($nonce, $decodedQrContent['nonce']);
        $this->assertSame($signature, $decodedQrContent['signature']);

        $this->assertDatabaseHas('proofs', [
            'id' => $proof->id,
            'status' => 'shared',
            'qr_nonce' => $nonce,
            'qr_signature' => $signature,
        ]);
    }

    public function test_resharing_shared_proof_updates_qr(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'generated');

        Sanctum::actingAs($user);

        $first = $this->postJson('/api/proof/share', [
            'proof_id' => $proof->id,
        ]);

        $first->assertOk();

        $firstNonce = $first->json('qr.qr_nonce');
        $firstSignature = $first->json('qr.qr_signature');

        $second = $this->postJson('/api/proof/share', [
            'proof_id' => $proof->id,
        ]);

        $second->assertOk();

        $secondNonce = $second->json('qr.qr_nonce');
        $secondSignature = $second->json('qr.qr_signature');

        $this->assertNotSame($firstNonce, $secondNonce);
        $this->assertNotSame($firstSignature, $secondSignature);
        $this->assertDatabaseHas('proofs', [
            'id' => $proof->id,
            'status' => 'shared',
            'qr_nonce' => $secondNonce,
            'qr_signature' => $secondSignature,
        ]);
    }
}
