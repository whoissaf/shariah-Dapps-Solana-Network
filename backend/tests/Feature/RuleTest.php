<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Document;
use App\Models\Identity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RuleTest extends TestCase
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

    private function createClaim(User $user, Identity $identity, string $claimType, array $payload, string $status = 'submitted'): Claim
    {
        return Claim::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => $claimType,
            'status' => $status,
            'payload' => $payload,
            'submitted_at' => now(),
        ]);
    }

    private function createDocument(Claim $claim): Document
    {
        return Document::create([
            'user_id' => $claim->user_id,
            'claim_id' => $claim->id,
            'document_type' => 'salary',
            'original_name' => 'document.pdf',
            'file_path' => 'documents/test/document.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'status' => 'uploaded',
        ]);
    }

    public function test_guest_cannot_validate_rule(): void
    {
        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => 1,
        ]);

        $response->assertUnauthorized();
    }

    public function test_user_cannot_validate_other_user_claim(): void
    {
        $owner = $this->createUser('owner@example.com');
        $ownerIdentity = $this->createIdentity($owner);
        $ownerClaim = $this->createClaim($owner, $ownerIdentity, 'income_threshold', [
            'monthly_income' => 5000000,
        ]);
        $this->createDocument($ownerClaim);

        $other = $this->createUser('other@example.com');

        Sanctum::actingAs($other);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $ownerClaim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim_id');
    }

    public function test_claim_not_found_for_user(): void
    {
        $user = $this->createUser();

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => 999999,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim_id');
    }

    public function test_user_cannot_validate_claim_without_document(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'income_threshold', [
            'monthly_income' => 5000000,
        ]);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('documents');
    }

    public function test_income_claim_can_be_eligible(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'income_threshold', [
            'monthly_income' => 5000000,
        ]);
        $this->createDocument($claim);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('eligible', true);
        $response->assertJsonPath('claim.status', 'eligible');
        $response->assertJsonPath('results.0.passed', true);

        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'status' => 'eligible',
        ]);
    }

    public function test_income_claim_can_be_ineligible_with_reason(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'income_threshold', [
            'monthly_income' => 4000000,
        ]);
        $this->createDocument($claim);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('eligible', false);
        $response->assertJsonPath('claim.status', 'ineligible');
        $response->assertJsonPath('results.0.passed', false);
        $response->assertJsonPath('results.0.reason', 'Monthly income does not meet the minimum threshold.');

        $this->assertDatabaseHas('claims', [
            'id' => $claim->id,
            'status' => 'ineligible',
        ]);
    }

    public function test_age_claim_can_be_eligible(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'age_minimum', [
            'date_of_birth' => '2000-01-01',
        ]);
        $this->createDocument($claim);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('eligible', true);
        $response->assertJsonPath('claim.status', 'eligible');
    }

    public function test_age_claim_can_be_ineligible(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'age_minimum', [
            'date_of_birth' => now()->subYears(20)->format('Y-m-d'),
        ]);
        $this->createDocument($claim);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('eligible', false);
        $response->assertJsonPath('claim.status', 'ineligible');
        $response->assertJsonPath('results.0.reason', 'Age does not meet the minimum requirement.');
    }

    public function test_business_category_claim_can_be_eligible(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'business_category_halal', [
            'business_category' => 'halal-food',
        ]);
        $this->createDocument($claim);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('eligible', true);
        $response->assertJsonPath('claim.status', 'eligible');
    }

    public function test_business_category_claim_can_be_ineligible(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'business_category_halal', [
            'business_category' => 'conventional',
        ]);
        $this->createDocument($claim);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('eligible', false);
        $response->assertJsonPath('claim.status', 'ineligible');
        $response->assertJsonPath('results.0.reason', 'Business category is not recognized as halal.');
    }

    public function test_financing_claim_can_be_eligible(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'no_active_restricted_financing', [
            'has_restricted_financing' => false,
        ]);
        $this->createDocument($claim);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('eligible', true);
        $response->assertJsonPath('claim.status', 'eligible');
    }

    public function test_financing_claim_can_be_ineligible(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'no_active_restricted_financing', [
            'has_restricted_financing' => true,
        ]);
        $this->createDocument($claim);

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertOk();
        $response->assertJsonPath('eligible', false);
        $response->assertJsonPath('claim.status', 'ineligible');
        $response->assertJsonPath('results.0.reason', 'Active restricted financing detected.');
    }

    public function test_proof_generated_claim_cannot_be_validated(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity, 'income_threshold', [
            'monthly_income' => 5000000,
        ], 'proof_generated');

        Sanctum::actingAs($user);

        $response = $this->postJson('/api/rules/validate', [
            'claim_id' => $claim->id,
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim');
    }
}
