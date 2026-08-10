<?php

namespace Tests\Feature;

use App\Models\Identity;
use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class IdentityTest extends TestCase
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

    private function createWallet(User $user, string $address = '0xabcdef0123456789abcdef0123456789abcdef01'): Wallet
    {
        return Wallet::create([
            'user_id' => $user->id,
            'wallet_address' => $address,
            'wallet_session' => 'wallet-session',
            'connected_at' => now(),
            'status' => 'connected',
        ]);
    }

    public function test_guest_cannot_create_identity(): void
    {
        $response = $this->postJson('/api/identity/create');

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_get_identity(): void
    {
        $response = $this->getJson('/api/identity');

        $response->assertUnauthorized();
    }

    public function test_user_can_create_identity_without_wallet(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/identity/create');

        $response->assertCreated();
        $response->assertJsonPath('created', true);
        $response->assertJsonPath('identity.status', 'active');
        $response->assertJsonPath('identity.wallet_id', null);
        $response->assertJsonStructure([
            'message',
            'created',
            'identity' => [
                'id',
                'anonymous_id',
                'wallet_id',
                'identity_commitment',
                'status',
                'created_at',
            ],
        ]);
        $response->assertJsonMissingPath('identity.identity_secret');

        $this->assertDatabaseHas('identities', [
            'user_id' => $user->id,
            'status' => 'active',
        ]);
    }

    public function test_user_can_create_identity_with_wallet_linked(): void
    {
        $user = $this->createUser();
        $wallet = $this->createWallet($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/identity/create');

        $response->assertCreated();
        $response->assertJsonPath('identity.wallet_id', $wallet->id);

        $this->assertDatabaseHas('identities', [
            'user_id' => $user->id,
            'wallet_id' => $wallet->id,
            'status' => 'active',
        ]);
    }

    public function test_duplicate_create_returns_existing_identity(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $first = $this->postJson('/api/identity/create');
        $first->assertCreated();

        $anonymousId = $first->json('identity.anonymous_id');
        $commitment = $first->json('identity.identity_commitment');

        $second = $this->postJson('/api/identity/create');
        $second->assertOk();
        $second->assertJsonPath('created', false);
        $second->assertJsonPath('identity.anonymous_id', $anonymousId);
        $second->assertJsonPath('identity.identity_commitment', $commitment);

        $this->assertDatabaseCount('identities', 1);
    }

    public function test_revoked_identity_allows_new_identity(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $first = $this->postJson('/api/identity/create');
        $first->assertCreated();

        $identity = Identity::where('user_id', $user->id)->first();
        $identity->forceFill([
            'status' => 'revoked',
        ])->save();

        $second = $this->postJson('/api/identity/create');
        $second->assertCreated();

        $this->assertDatabaseCount('identities', 2);

        $activeCount = Identity::where('user_id', $user->id)
            ->where('status', 'active')
            ->count();

        $this->assertSame(1, $activeCount);
    }

    public function test_user_can_get_current_identity(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $created = $this->postJson('/api/identity/create');
        $created->assertCreated();

        $anonymousId = $created->json('identity.anonymous_id');

        $response = $this->getJson('/api/identity');

        $response->assertOk();
        $response->assertJsonPath('identity.anonymous_id', $anonymousId);
        $response->assertJsonMissingPath('identity.identity_secret');
    }

    public function test_user_can_get_null_identity_when_not_created(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/identity');

        $response->assertOk();
        $response->assertJsonPath('identity', null);
    }
}
