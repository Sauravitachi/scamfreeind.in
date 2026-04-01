<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $table) {
            $table->id();

            $table->string('tag', 40);
            $table->string('key', 255);
            $table->string('value', 1024)->nullable();

            $table->timestamps();

            // Optional but recommended
            $table->unique(['tag', 'key']);
            $table->index('tag');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');
    }
};
