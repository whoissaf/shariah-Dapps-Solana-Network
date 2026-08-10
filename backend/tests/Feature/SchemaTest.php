<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SchemaTest extends TestCase
{
    use RefreshDatabase;

    public function test_core_tables_exist(): void
    {
        $tables = [
            'users',
            'otp_codes',
            'wallets',
            'identities',
            'claims',
            'documents',
            'rules',
            'proofs',
            'blockchain_logs',
            'verifications',
            'verification_events',
            'audit_trails',
            'reports',
        ];

        foreach ($tables as $table) {
            $this->assertTrue(Schema::hasTable($table), $table);
        }
    }
}
