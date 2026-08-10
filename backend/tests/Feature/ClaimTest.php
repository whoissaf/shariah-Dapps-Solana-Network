<?php

namespace Tests\Feature;

use App\Models\Identity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class ClaimTest extends TestCase
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

    public function test_guest_cannot_create_claim(): void
    {
        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'income_threshold',
            'payload' => [
                'monthly_income' => 5000000,
            ],
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_list_claims(): void
    {
        $response = $this->getJson('/api/claims');

        $response->assertUnauthorized();
    }

    public function test_user_cannot_create_claim_without_identity(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'income_threshold',
            'payload' => [
                'monthly_income' => 5000000,
            ],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('identity');
    }

    public function test_user_can_create_income_claim(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'income_threshold',
            'payload' => [
                'monthly_income' => 5000000,
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('claim.claim_type', 'income_threshold');
        $response->assertJsonPath('claim.status', 'submitted');
        $response->assertJsonPath('claim.identity_id', $identity->id);
        $response->assertJsonPath('claim.payload.monthly_income', 5000000);

        $this->assertDatabaseHas('claims', [
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => 'income_threshold',
            'status' => 'submitted',
        ]);
    }

    public function test_user_can_create_age_claim(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'age_minimum',
            'payload' => [
                'date_of_birth' => '2000-01-01',
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('claim.claim_type', 'age_minimum');
        $response->assertJsonPath('claim.payload.date_of_birth', '2000-01-01');
    }

    public function test_user_can_create_business_category_claim(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'business_category_halal',
            'payload' => [
                'business_category' => 'halal-food',
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('claim.claim_type', 'business_category_halal');
        $response->assertJsonPath('claim.payload.business_category', 'halal-food');
    }

    public function test_user_can_create_no_restricted_financing_claim(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'no_active_restricted_financing',
            'payload' => [
                'has_restricted_financing' => false,
            ],
        ]);

        $response->assertCreated();
        $response->assertJsonPath('claim.claim_type', 'no_active_restricted_financing');
        $response->assertJsonPath('claim.payload.has_restricted_financing', false);
    }

    public function test_claim_type_must_be_valid(): void
    {
        $user = $this->createUser();
        $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'invalid_claim',
            'payload' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim_type');
    }

    public function test_income_claim_requires_monthly_income(): void
    {
        $user = $this->createUser();
        $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'income_threshold',
            'payload' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payload.monthly_income');
    }

    public function test_age_claim_requires_date_of_birth(): void
    {
        $user = $this->createUser();
        $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'age_minimum',
            'payload' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payload.date_of_birth');
    }

    public function test_business_claim_requires_business_category(): void
    {
        $user = $this->createUser();
        $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'business_category_halal',
            'payload' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payload.business_category');
    }

    public function test_financing_claim_requires_has_restricted_financing(): void
    {
        $user = $this->createUser();
        $this->createIdentity($user);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/claims/create', [
            'claim_type' => 'no_active_restricted_financing',
            'payload' => [],
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('payload.has_restricted_financing');
    }

    public function test_duplicate_active_claim_type_is_rejected(): void
    {
        $user = $this->createUser();
        $this->createIdentity($user);

        Sanctum::actingAs($user);

        $payload = [
            'claim_type' => 'income_threshold',
            'payload' => [
                'monthly_income' => 5000000,
            ],
        ];

        $this->postJson('/api/claims/create', $payload)->assertCreated();
        $this->postJson('/api/claims/create', $payload)->assertStatus(422);
    }

    public function test_user_can_list_claims(): void
    {
        $user = $this->createUser();
        $this->createIdentity($user);

        Sanctum::actingAs($user);

        $this->postJson('/api/claims/create', [
            'claim_type' => 'income_threshold',
            'payload' => [
                'monthly_income' => 5000000,
            ],
        ])->assertCreated();

        $this->postJson('/api/claims/create', [
            'claim_type' => 'age_minimum',
            'payload' => [
                'date_of_birth' => '2000-01-01',
            ],
        ])->assertCreated();

        $response = $this->getJson('/api/claims');

        $response->assertOk();
        $response->assertJsonCount(2, 'claims');
        $response->assertJsonStructure([
            'message',
            'claims' => [
                '*' => [
                    'id',
                    'identity_id',
                    'claim_type',
                    'status',
                    'payload',
                    'submitted_at',
                    'created_at',
                ],
            ],
        ]);
    }
}
