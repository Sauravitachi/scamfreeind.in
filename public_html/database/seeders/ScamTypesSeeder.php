<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScamTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = now();

        $types = [
            ['id' => 1, 'slug' => 'insurance_scam', 'title' => 'Insurance Scam', 'is_default' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'slug' => 'bank_scam', 'title' => 'Bank Scam', 'is_default' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'slug' => 'pension_scam', 'title' => 'Pension Scam', 'is_default' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'slug' => 'loan_scam', 'title' => 'Loan Scam', 'is_default' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'slug' => 'job_scam', 'title' => 'Job Scam', 'is_default' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 6, 'slug' => 'stock_trading_scam', 'title' => 'Stock Trading Scam', 'is_default' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 7, 'slug' => 'lottery_scam', 'title' => 'Lottery Scam', 'is_default' => 0, 'created_at' => $now, 'updated_at' => $now],
            ['id' => 8, 'slug' => 'other_scam', 'title' => 'Other Scam', 'is_default' => 0, 'created_at' => $now, 'updated_at' => $now],
        ];

        foreach ($types as $type) {
            DB::table('scam_types')->updateOrInsert(
                ['id' => $type['id']],
                $type
            );
        }
    }
}
