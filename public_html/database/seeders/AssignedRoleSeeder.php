<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use App\Models\User;
use Illuminate\Database\Seeder;

class AssignedRoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        // $arshpreet = User::factory()->create([
        //     'name' => 'Arshpreet',
        //     'email' => 'sfi.arshpreet@gmail.com',
        //     'username' => 'arshpreet',
        //     'country_code' => '+91',
        //     'phone_number' => '9999999999',
        //     'password' => 'password', 
        //     'status' => true,
        // ]);
        // $arshpreet->assignRole('Sales Executive');

        // $jatin = User::factory()->create([
        //     'name' => 'Jatin',
        //     'email' => 'sfi.jatin@gmail.com',
        //     'username' => 'jatin',
        //     'country_code' => '+91',
        //     'phone_number' => '8888888888',
        //     'password' => 'password',
        //     'status' => true,
        // ]);
        // $jatin->assignRole('Drafting Executive');

        // $dheeraj = User::factory()->create([
        //     'name' => 'Dheeraj',
        //     'email' => 'sfi.dheeraj@gmail.com',
        //     'username' => 'dheeraj',
        //     'country_code' => '+91',
        //     'phone_number' => '7777777777',
        //     'password' => 'password',
        //     'status' => true,
        // ]);
        // $dheeraj->assignRole('Service Executive');

        // $schain = User::factory()->create([
        //     'name' => 'Schain',
        //     'email' => 'sfi.schain@gmail.com',
        //     'username' => 'schain',
        //     'country_code' => '+91',
        //     'phone_number' => '6666666666',
        //     'password' => 'password',
        //     'status' => true,
        // ]);
        // $schain->assignRole('MIS');

        // $ankitsales = User::factory()->create([
        //     'name' => 'Ankit Sales',
        //     'email' => 'sfi.ankitsales@gmail.com',
        //     'username' => 'ankitsales',
        //     'country_code' => '+91',
        //     'phone_number' => '5555555555',
        //     'password' => 'scamfree',
        //     'status' => true,
        // ]);
        // $ankitsales->assignRole('Sales Executive');

    //     $sachinsales = User::factory()->create([
    //         'name' => 'sachin Sales',
    //         'email' => 'sfi.sachinsales@gmail.com',
    //         'username' => 'sachinsales',
    //         'country_code' => '+91',
    //         'phone_number' => '5555555555',
    //         'password' => 'scamfree',
    //         'status' => true,
    //     ]);
    //     $sachinsales->assignRole('Sales Executive');
    // }

    // $ronit = User::factory()->create([
    //     'name'=>'Ronit',
    //     'email'=>'sfi.ronit@gmail.com',
    //     'username'=>'ronit',
    //     'country_code'=>'+91',
    //     'phone_number'=>'4444444444',
    //     'password'=>'scamfree',
    //     'status'=>true,
    // ]);
    // $ronit->assignRole('Sales Executive');
    // }

    $rimpaalkaur = User::factory()->create([
        'name'=>'Rimpaal Kaur',
        'email'=>'sfi.rimpaalkaur@gmail.com',
        'username'=>'rimpaalkaur',
        'country_code'=>'+91',
        'phone_number'=>'3333333333',
        'password'=>'scamfree',
        'status'=>true,
    ]);
    $rimpaalkaur->assignRole('Sales Executive');
    }
}