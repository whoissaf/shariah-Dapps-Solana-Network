<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DatabaseConnectionTest extends TestCase
{
    public function test_database_connection_works(): void
    {
        $pdo = DB::connection()->getPdo();

        $this->assertNotNull($pdo);
    }
}
