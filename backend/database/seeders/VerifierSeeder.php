<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class VerifierSeeder extends Seeder
{
    public function run(): void
    {
        User::firstOrCreate([
            'email' => 'verifier@example.com',
        ], [
            'name' => 'Verifier Demo',
            'password' => 'password123',
            'role' => 'verifier',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
    }
}
