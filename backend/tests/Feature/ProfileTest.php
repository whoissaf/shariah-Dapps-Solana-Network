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

class ProfileTest extends TestCase
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

    private function createClaim(User $user, Identity $identity, string $claimType, string $status): Claim
    {
        return Claim::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => $claimType,
            'status' => $status,
            'payload' => [
                'monthly_income' => 5000000,
            ],
            'submitted_at' => now(),
        ]);
    }

    private function createProof(User $user, Identity $identity, Claim $claim, string $status): Proof
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

    public function test_guest_cannot_access_profile(): void
    {
        $response = $this->getJson('/api/profile');

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_logout(): void
    {
        $response = $this->postJson('/api/auth/logout');

        $response->assertUnauthorized();
    }

    public function test_user_can_get_profile_with_empty_state(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertOk();
        $response->assertJsonPath('user.email', $user->email);
        $response->assertJsonPath('user.role', 'user');
        $response->assertJsonPath('user.email_verified', true);
        $response->assertJsonPath('wallet', null);
        $response->assertJsonPath('identity', null);
        $response->assertJsonPath('summary.claims_total', 0);
        $response->assertJsonPath('summary.proofs_total', 0);
        $response->assertJsonPath('meta.app_version', '1.0.0-mvp');
        $response->assertJsonStructure([
            'message',
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'email_verified',
                'created_at',
            ],
            'wallet',
            'identity',
            'summary',
            'meta',
        ]);
        $response->assertJsonMissingPath('user.password');
    }

    public function test_profile_includes_wallet_and_identity(): void
    {
        $user = $this->createUser();
        $wallet = $this->createWallet($user);
        $identity = $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertOk();
        $response->assertJsonPath('wallet.wallet_address', $wallet->wallet_address);
        $response->assertJsonPath('wallet.status', 'connected');
        $response->assertJsonPath('identity.anonymous_id', $identity->anonymous_id);
        $response->assertJsonPath('identity.identity_commitment', $identity->identity_commitment);
        $response->assertJsonPath('identity.status', 'active');
        $response->assertJsonMissingPath('identity.identity_secret');
        $response->assertJsonMissingPath('user.password');
    }

    public function test_profile_includes_claim_and_proof_summary(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);

        $eligibleClaim = $this->createClaim($user, $identity, 'income_threshold', 'eligible');
        $ineligibleClaim = $this->createClaim($user, $identity, 'age_minimum', 'ineligible');

        $this->createProof($user, $identity, $eligibleClaim, 'generated');
        $this->createProof($user, $identity, $ineligibleClaim, 'shared');

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/profile');

        $response->assertOk();
        $response->assertJsonPath('summary.claims_total', 2);
        $response->assertJsonPath('summary.claims_eligible', 1);
        $response->assertJsonPath('summary.claims_ineligible', 1);
        $response->assertJsonPath('summary.proofs_total', 2);
        $response->assertJsonPath('summary.proofs_generated', 1);
        $response->assertJsonPath('summary.proofs_shared', 1);
    }

    public function test_logout_revokes_token(): void
    {
        $user = $this->createUser();

        $token = $user->createToken('auth-token')->plainTextToken;

        $logoutResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->postJson('/api/auth/logout');

        $logoutResponse->assertOk();
        $logoutResponse->assertJsonPath('message', 'Logout success.');

        $this->assertDatabaseCount('personal_access_tokens', 0);

        $this->app['auth']->forgetGuards();

        $profileResponse = $this->withHeaders([
            'Authorization' => 'Bearer ' . $token,
        ])->getJson('/api/profile');

        $profileResponse->assertUnauthorized();
    }
}
