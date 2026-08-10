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

class HistoryTest extends TestCase
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

    private function createVerification(Proof $proof, string $status): Verification
    {
        return Verification::create([
            'proof_id' => $proof->id,
            'status' => $status,
            'verified_at' => in_array($status, ['verified', 'rejected']) ? now() : null,
        ]);
    }

    public function test_guest_cannot_access_history(): void
    {
        $response = $this->getJson('/api/history');

        $response->assertUnauthorized();
    }

    public function test_user_can_get_empty_history(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonCount(0, 'history');
    }

    public function test_user_can_only_see_own_history(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $userProof = $this->createProof($user, $identity, $claim);

        $other = $this->createUser('other@example.com');
        $otherIdentity = $this->createIdentity($other);
        $otherClaim = $this->createClaim($other, $otherIdentity);
        $this->createProof($other, $otherIdentity, $otherClaim);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonCount(1, 'history');
        $response->assertJsonPath('history.0.proof_id', $userProof->id);
    }

    public function test_generated_proof_appears_in_history(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'generated');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonCount(1, 'history');
        $response->assertJsonPath('history.0.proof_id', $proof->id);
        $response->assertJsonPath('history.0.claim_type', 'income_threshold');
        $response->assertJsonPath('history.0.proof_status', 'generated');
        $response->assertJsonPath('history.0.display_status', 'generated');
    }

    public function test_shared_proof_appears_in_history(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $this->createProof($user, $identity, $claim, 'shared');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonPath('history.0.display_status', 'shared');
    }

    public function test_expired_proof_appears_as_expired(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $this->createProof($user, $identity, $claim, 'expired');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonPath('history.0.display_status', 'expired');
    }

    public function test_verified_verification_overrides_proof_status(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'shared');
        $this->createVerification($proof, 'verified');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonPath('history.0.proof_status', 'shared');
        $response->assertJsonPath('history.0.verification_status', 'verified');
        $response->assertJsonPath('history.0.display_status', 'verified');
    }

    public function test_rejected_verification_overrides_proof_status(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'shared');
        $this->createVerification($proof, 'rejected');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonPath('history.0.proof_status', 'shared');
        $response->assertJsonPath('history.0.verification_status', 'rejected');
        $response->assertJsonPath('history.0.display_status', 'rejected');
    }

    public function test_pending_verification_does_not_override_proof_status(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'generated');
        $this->createVerification($proof, 'pending');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonPath('history.0.verification_status', 'pending');
        $response->assertJsonPath('history.0.display_status', 'generated');
    }

    public function test_latest_verification_status_wins(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);
        $proof = $this->createProof($user, $identity, $claim, 'shared');

        $this->createVerification($proof, 'rejected');
        $this->createVerification($proof, 'verified');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/history');

        $response->assertOk();
        $response->assertJsonPath('history.0.verification_status', 'verified');
        $response->assertJsonPath('history.0.display_status', 'verified');
    }
}
