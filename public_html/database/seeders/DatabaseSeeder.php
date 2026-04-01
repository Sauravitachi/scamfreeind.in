<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        $superAdmin = User::factory()->create([
            'name' => 'Ankit Sharma',
            'email' => 'sfi.ankitsharma@gmail.com',
            'username' => 'ankitsharma',
            'country_code' => '+91',
            'phone_number' => '6280084608',
            'password' => 'password', // Change to a secure password if needed
            'status' => true,
        ]);

        $superAdmin->assignRole('Super Admin');

        $this->call([
            ScamTypesSeeder::class,
            AppUiDataSeeder::class,
        ]);
    }
}
