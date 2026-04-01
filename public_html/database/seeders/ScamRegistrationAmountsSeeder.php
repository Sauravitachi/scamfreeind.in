<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ScamRegistrationAmountsSeeder extends Seeder
{
    public function run(): void
    {
        $now = Carbon::now();

        DB::table('scam_registration_amounts')->truncate();

        DB::table('scam_registration_amounts')->insert([
            ['title' => '999',   'amount' => 999.00,   'points' => 1.00,  'description' => '999',   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '1999',  'amount' => 1999.00,  'points' => 2.00,  'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '2999',  'amount' => 2999.00,  'points' => 3.00,  'description' => '2999', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '3999',  'amount' => 3999.00,  'points' => 4.00,  'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '4999',  'amount' => 4999.00,  'points' => 5.00,  'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '5999',  'amount' => 5999.00,  'points' => 6.00,  'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '6999',  'amount' => 6999.00,  'points' => 7.00,  'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '7999',  'amount' => 7999.00,  'points' => 8.00,  'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '8999',  'amount' => 8999.00,  'points' => 9.00,  'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '9999',  'amount' => 9999.00,  'points' => 10.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],

            ['title' => '10999', 'amount' => 10999.00, 'points' => 11.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '11999', 'amount' => 11999.00, 'points' => 12.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '12999', 'amount' => 12999.00, 'points' => 13.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '13999', 'amount' => 13999.00, 'points' => 14.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '14999', 'amount' => 14999.00, 'points' => 15.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '15999', 'amount' => 15999.00, 'points' => 16.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '16999', 'amount' => 16999.00, 'points' => 17.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '17999', 'amount' => 17999.00, 'points' => 18.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '18999', 'amount' => 18999.00, 'points' => 19.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '19999', 'amount' => 19999.00, 'points' => 20.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],

            ['title' => '20999', 'amount' => 20999.00, 'points' => 21.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '21999', 'amount' => 21999.00, 'points' => 22.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '22999', 'amount' => 22999.00, 'points' => 23.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '23999', 'amount' => 23999.00, 'points' => 24.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '24999', 'amount' => 24999.00, 'points' => 25.00, 'description' => null,   'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],

            ['title' => '29999', 'amount' => 29999.00, 'points' => 30.00, 'description' => '29999', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '34999', 'amount' => 34999.00, 'points' => 35.00, 'description' => '34999', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '39999', 'amount' => 39999.00, 'points' => 40.00, 'description' => '39999', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '44999', 'amount' => 44999.00, 'points' => 45.00, 'description' => '44999', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '49999', 'amount' => 49999.00, 'points' => 50.00, 'description' => '49999', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '54999', 'amount' => 54999.00, 'points' => 55.00, 'description' => '54999', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
            ['title' => '59999', 'amount' => 59999.00, 'points' => 60.00, 'description' => '59999', 'is_active' => 1, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
