set -e
cd /home/ilham/Documents/hackathon/backend

rm -f database/migrations/2024_01_01_000001_create_users_table.php
rm -f database/migrations/2024_01_01_000002_create_wallets_table.php
rm -f database/migrations/2024_01_01_000003_create_identities_table.php
rm -f database/migrations/2024_01_01_000004_create_claims_table.php
rm -f database/migrations/2024_01_01_000005_create_documents_table.php
rm -f database/migrations/2024_01_01_000006_create_proofs_table.php
rm -f database/migrations/2024_01_01_000007_create_blockchain_logs_table.php

cat <<'MIGRATION' > database/migrations/2024_01_01_000001_create_users_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
MIGRATION

cat <<'MIGRATION' > database/migrations/2024_01_01_000002_create_wallets_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('wallet_address')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wallets');
    }
};
MIGRATION

cat <<'MIGRATION' > database/migrations/2024_01_01_000003_create_identities_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('identities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('commitment')->unique();
            $table->string('secret');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('identities');
    }
};
MIGRATION

cat <<'MIGRATION' > database/migrations/2024_01_01_000004_create_claims_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('claim_type');
            $table->json('claim_value')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claims');
    }
};
MIGRATION

cat <<'MIGRATION' > database/migrations/2024_01_01_000005_create_documents_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('claim_id')->constrained()->onDelete('cascade');
            $table->string('file_path');
            $table->string('file_type');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
MIGRATION

cat <<'MIGRATION' > database/migrations/2024_01_01_000006_create_proofs_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('proofs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('claim_id')->constrained()->onDelete('cascade');
            $table->string('proof_hash')->unique();
            $table->json('proof_data')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('proofs');
    }
};
MIGRATION

cat <<'MIGRATION' > database/migrations/2024_01_01_000007_create_blockchain_logs_table.php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blockchain_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('proof_id')->constrained()->onDelete('cascade');
            $table->string('tx_hash')->unique();
            $table->bigInteger('block_number')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blockchain_logs');
    }
};
MIGRATION

