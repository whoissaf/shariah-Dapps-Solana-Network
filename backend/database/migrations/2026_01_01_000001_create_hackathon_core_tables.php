<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otp_codes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('email');
            $table->string('code');
            $table->string('purpose')->default('register');
            $table->timestamp('expires_at');
            $table->timestamp('used_at')->nullable();
            $table->timestamps();

            $table->index(['email', 'purpose']);
        });

        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('wallet_address')->unique();
            $table->string('wallet_session')->nullable();
            $table->timestamp('connected_at')->nullable();
            $table->enum('status', ['connected', 'disconnected', 'revoked'])->default('connected');
            $table->timestamps();
        });

        Schema::create('identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('wallet_id')->nullable()->constrained()->nullOnDelete();
            $table->uuid('anonymous_id')->unique();
            $table->string('identity_secret');
            $table->string('identity_commitment')->unique();
            $table->enum('status', ['active', 'revoked', 'expired'])->default('active');
            $table->timestamps();
        });

        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('identity_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('claim_type', [
                'income_threshold',
                'age_minimum',
                'business_category_halal',
                'no_active_restricted_financing',
            ]);
            $table->enum('status', [
                'draft',
                'submitted',
                'eligible',
                'ineligible',
                'proof_generated',
                'expired',
            ])->default('draft');
            $table->jsonb('payload')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'claim_type']);
        });

        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->enum('document_type', [
                'salary',
                'business_certificate',
                'license',
                'other',
            ]);
            $table->string('original_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->enum('status', ['uploaded', 'accepted', 'rejected'])->default('uploaded');
            $table->timestamps();

            $table->index(['claim_id', 'document_type']);
        });

        Schema::create('rules', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('rule_type', [
                'income_threshold',
                'age_minimum',
                'business_category_halal',
                'no_active_restricted_financing',
            ]);
            $table->jsonb('parameters')->nullable();
            $table->integer('position')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('claim_id')->constrained()->cascadeOnDelete();
            $table->foreignId('identity_id')->nullable()->constrained()->nullOnDelete();
            $table->string('proof_hash')->unique();
            $table->jsonb('proof_payload')->nullable();
            $table->string('qr_signature')->nullable();
            $table->string('qr_nonce')->nullable();
            $table->timestamp('qr_expires_at')->nullable();
            $table->enum('status', [
                'generated',
                'shared',
                'verified',
                'rejected',
                'expired',
            ])->default('generated');
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });

        Schema::create('blockchain_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proof_id')->constrained()->cascadeOnDelete();
            $table->string('network')->default('ethereum');
            $table->string('contract_address')->nullable();
            $table->string('tx_hash')->nullable();
            $table->bigInteger('block_number')->nullable();
            $table->string('event_name')->nullable();
            $table->jsonb('payload')->nullable();
            $table->enum('status', ['pending', 'confirmed', 'failed'])->default('pending');
            $table->timestamps();

            $table->index('tx_hash');
        });

        Schema::create('verifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proof_id')->constrained()->cascadeOnDelete();
            $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['pending', 'verified', 'rejected'])->default('pending');
            $table->jsonb('result')->nullable();
            $table->jsonb('ai_explanation')->nullable();
            $table->text('reject_reason')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });

        Schema::create('verification_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verification_id')->constrained()->cascadeOnDelete();
            $table->string('event_type');
            $table->string('actor')->nullable();
            $table->text('message')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();
        });

        Schema::create('audit_trails', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action');
            $table->string('entity_type');
            $table->unsignedBigInteger('entity_id');
            $table->string('ethereum_tx_hash')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->jsonb('metadata')->nullable();
            $table->timestamps();

            $table->index(['entity_type', 'entity_id']);
        });

        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('verifier_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('report_type');
            $table->jsonb('filters')->nullable();
            $table->string('file_path')->nullable();
            $table->enum('status', ['queued', 'processing', 'completed', 'failed'])->default('queued');
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reports');
        Schema::dropIfExists('audit_trails');
        Schema::dropIfExists('verification_events');
        Schema::dropIfExists('verifications');
        Schema::dropIfExists('blockchain_logs');
        Schema::dropIfExists('proofs');
        Schema::dropIfExists('rules');
        Schema::dropIfExists('documents');
        Schema::dropIfExists('claims');
        Schema::dropIfExists('identities');
        Schema::dropIfExists('wallets');
        Schema::dropIfExists('otp_codes');
    }
};
