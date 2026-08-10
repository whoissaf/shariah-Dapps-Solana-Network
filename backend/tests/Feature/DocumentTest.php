<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\Document;
use App\Models\Identity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DocumentTest extends TestCase
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

    private function createClaim(User $user, Identity $identity, string $claimType = 'income_threshold'): Claim
    {
        return Claim::create([
            'user_id' => $user->id,
            'identity_id' => $identity->id,
            'claim_type' => $claimType,
            'status' => 'submitted',
            'payload' => [
                'monthly_income' => 5000000,
            ],
            'submitted_at' => now(),
        ]);
    }

    public function test_guest_cannot_upload_document(): void
    {
        $response = $this->post('/api/documents/upload', [], [
            'Accept' => 'application/json',
        ]);

        $response->assertUnauthorized();
    }

    public function test_guest_cannot_list_documents(): void
    {
        $response = $this->getJson('/api/documents');

        $response->assertUnauthorized();
    }

    public function test_user_can_upload_document(): void
    {
        Storage::fake('local');

        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('salary.pdf', 100, 'application/pdf');

        $response = $this->post('/api/documents/upload', [
            'claim_id' => $claim->id,
            'document_type' => 'salary',
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertCreated();
        $response->assertJsonPath('document.claim_id', $claim->id);
        $response->assertJsonPath('document.document_type', 'salary');
        $response->assertJsonPath('document.status', 'uploaded');
        $response->assertJsonStructure([
            'message',
            'document' => [
                'id',
                'claim_id',
                'document_type',
                'original_name',
                'file_path',
                'mime_type',
                'size_bytes',
                'status',
                'created_at',
            ],
        ]);

        $document = Document::first();

        Storage::disk('local')->assertExists($document->file_path);

        $this->assertDatabaseHas('documents', [
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'document_type' => 'salary',
            'status' => 'uploaded',
        ]);
    }

    public function test_user_cannot_upload_document_to_other_user_claim(): void
    {
        Storage::fake('local');

        $owner = $this->createUser('owner@example.com');
        $ownerIdentity = $this->createIdentity($owner);
        $ownerClaim = $this->createClaim($owner, $ownerIdentity);

        $other = $this->createUser('other@example.com');

        Sanctum::actingAs($other);

        $file = UploadedFile::fake()->create('salary.pdf', 100, 'application/pdf');

        $response = $this->post('/api/documents/upload', [
            'claim_id' => $ownerClaim->id,
            'document_type' => 'salary',
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('claim_id');
    }

    public function test_document_type_must_be_valid(): void
    {
        Storage::fake('local');

        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('salary.pdf', 100, 'application/pdf');

        $response = $this->post('/api/documents/upload', [
            'claim_id' => $claim->id,
            'document_type' => 'invalid_type',
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('document_type');
    }

    public function test_file_is_required(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);

        Sanctum::actingAs($user);

        $response = $this->post('/api/documents/upload', [
            'claim_id' => $claim->id,
            'document_type' => 'salary',
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_file_type_must_be_allowed(): void
    {
        Storage::fake('local');

        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('malware.txt', 100, 'text/plain');

        $response = $this->post('/api/documents/upload', [
            'claim_id' => $claim->id,
            'document_type' => 'salary',
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_file_size_cannot_exceed_limit(): void
    {
        Storage::fake('local');

        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);

        Sanctum::actingAs($user);

        $file = UploadedFile::fake()->create('large.pdf', 6000, 'application/pdf');

        $response = $this->post('/api/documents/upload', [
            'claim_id' => $claim->id,
            'document_type' => 'salary',
            'file' => $file,
        ], [
            'Accept' => 'application/json',
        ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors('file');
    }

    public function test_user_can_list_own_documents_only(): void
    {
        $user = $this->createUser();
        $identity = $this->createIdentity($user);
        $claim = $this->createClaim($user, $identity);

        $other = $this->createUser('other@example.com');
        $otherIdentity = $this->createIdentity($other);
        $otherClaim = $this->createClaim($other, $otherIdentity);

        Document::create([
            'user_id' => $user->id,
            'claim_id' => $claim->id,
            'document_type' => 'salary',
            'original_name' => 'salary.pdf',
            'file_path' => 'documents/user/salary.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'status' => 'uploaded',
        ]);

        Document::create([
            'user_id' => $other->id,
            'claim_id' => $otherClaim->id,
            'document_type' => 'license',
            'original_name' => 'license.pdf',
            'file_path' => 'documents/other/license.pdf',
            'mime_type' => 'application/pdf',
            'size_bytes' => 1024,
            'status' => 'uploaded',
        ]);

        Sanctum::actingAs($user);

        $response = $this->getJson('/api/documents');

        $response->assertOk();
        $response->assertJsonCount(1, 'documents');
        $response->assertJsonPath('documents.0.claim_id', $claim->id);
        $response->assertJsonStructure([
            'message',
            'documents' => [
                '*' => [
                    'id',
                    'claim_id',
                    'document_type',
                    'original_name',
                    'file_path',
                    'mime_type',
                    'size_bytes',
                    'status',
                    'created_at',
                ],
            ],
        ]);
    }
}
