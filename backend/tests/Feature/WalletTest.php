<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class WalletTest extends TestCase
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

    public function test_guest_cannot_connect_wallet(): void
    {
        $response = $this->postJson('/api/wallet/connect', [
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_get_wallet_profile(): void
    {
        $response = $this->getJson('/api/wallet/profile');

        $response->assertUnauthorized();
    }

    public function test_user_can_connect_wallet(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/connect', [
            'wallet_address' => '0xABCDEF0123456789ABCDEF0123456789ABCDEF01',
        ]);

        $response->assertOk();
        $response->assertJsonPath('wallet.wallet_address', '0xabcdef0123456789abcdef0123456789abcdef01');
        $response->assertJsonStructure([
            'message',
            'wallet' => [
                'id',
                'wallet_address',
                'status',
                'connected_at',
                'wallet_session',
            ],
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
            'status' => 'connected',
        ]);
    }

    public function test_user_can_update_wallet_address(): void
    {
        $user = $this->createUser();

        Wallet::create([
            'user_id' => $user->id,
            'wallet_address' => '0x1111111111111111111111111111111111111111',
            'wallet_session' => 'old-session',
            'connected_at' => now(),
            'status' => 'connected',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/connect', [
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
        ]);

        $response->assertOk();

        $this->assertDatabaseMissing('wallets', [
            'user_id' => $user->id,
            'wallet_address' => '0x1111111111111111111111111111111111111111',
        ]);

        $this->assertDatabaseHas('wallets', [
            'user_id' => $user->id,
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
            'status' => 'connected',
        ]);
    }

    public function test_user_can_reconnect_same_wallet_and_get_new_session(): void
    {
        $user = $this->createUser();

        $wallet = Wallet::create([
            'user_id' => $user->id,
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
            'wallet_session' => 'old-session',
            'connected_at' => now(),
            'status' => 'connected',
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/connect', [
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
        ]);

        $response->assertOk();

        $newSession = $response->json('wallet.wallet_session');

        $this->assertNotEquals($wallet->wallet_session, $newSession);
    }

    public function test_wallet_address_must_be_valid(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/wallet/connect', [
            'wallet_address' => '0xinvalid',
        ]);

        $response->assertStatus(422);
    }

    public function test_wallet_address_cannot_be_used_by_other_user(): void
    {
        $owner = $this->createUser('owner@example.com');
        $other = $this->createUser('other@example.com');

        Wallet::create([
            'user_id' => $owner->id,
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
            'wallet_session' => 'owner-session',
            'connected_at' => now(),
            'status' => 'connected',
        ]);

        Sanctum::actingAs($other);

        $response = $this->postJson('/api/wallet/connect', [
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
        ]);

        $response->assertStatus(422);
    }

    public function test_user_can_get_wallet_profile(): void
    {
        $user = $this->createUser();

        Wallet::create([
            'user_id' => $user->id,
            'wallet_address' => '0xabcdef0123456789abcdef0123456789abcdef01',
            'wallet_session' => 'session',
            'connected_at' => now(),
            'status' => 'connected',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallet/profile');

        $response->assertOk();
        $response->assertJsonPath('wallet.wallet_address', '0xabcdef0123456789abcdef0123456789abcdef01');
        $response->assertJsonStructure([
            'message',
            'wallet' => [
                'id',
                'wallet_address',
                'status',
                'connected_at',
            ],
        ]);
    }

    public function test_user_can_get_empty_wallet_profile(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/wallet/profile');

        $response->assertOk();
        $response->assertJsonPath('wallet', null);
    }
}
