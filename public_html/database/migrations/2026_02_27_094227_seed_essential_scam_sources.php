<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('scam_sources')->insertOrIgnore([
            ['slug' => 'whatsapp', 'title' => 'WhatsApp', 'indicator_color' => 'success', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'instagram', 'title' => 'Instagram', 'indicator_color' => 'danger', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'facebook', 'title' => 'Facebook', 'indicator_color' => 'primary', 'created_at' => now(), 'updated_at' => now()],
            ['slug' => 'website', 'title' => 'Website', 'indicator_color' => 'info', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
         DB::table('scam_sources')->whereIn('slug', ['whatsapp', 'instagram', 'facebook', 'website'])->delete();
    }
};
