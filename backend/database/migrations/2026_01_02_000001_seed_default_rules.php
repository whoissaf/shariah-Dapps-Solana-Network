<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now()->toDateTimeString();

        DB::table('rules')->insert([
            [
                'code' => 'income_threshold',
                'name' => 'Income Threshold',
                'description' => 'Monthly income must meet the minimum threshold.',
                'rule_type' => 'income_threshold',
                'parameters' => json_encode([
                    'min_monthly_income' => 5000000,
                ]),
                'position' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'age_minimum',
                'name' => 'Age Minimum',
                'description' => 'User age must meet the minimum requirement.',
                'rule_type' => 'age_minimum',
                'parameters' => json_encode([
                    'min_age' => 21,
                ]),
                'position' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'business_category_halal',
                'name' => 'Business Category Halal',
                'description' => 'Business category must be recognized as halal.',
                'rule_type' => 'business_category_halal',
                'parameters' => json_encode([
                    'keyword' => 'halal',
                ]),
                'position' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'code' => 'no_active_restricted_financing',
                'name' => 'No Active Restricted Financing',
                'description' => 'User must not have active restricted financing.',
                'rule_type' => 'no_active_restricted_financing',
                'parameters' => json_encode([
                    'expected' => false,
                ]),
                'position' => 1,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    public function down(): void
    {
        DB::table('rules')->whereIn('code', [
            'income_threshold',
            'age_minimum',
            'business_category_halal',
            'no_active_restricted_financing',
        ])->delete();
    }
};
