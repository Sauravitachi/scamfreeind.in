<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_preferences', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->enum('key', [
                'theme',
                'menu_layout',
            ]);

            $table->string('value', 2000)->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES & CONSTRAINTS
            |--------------------------------------------------------------------------
            */
            $table->index('user_id');
            $table->unique(['user_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_preferences');
    }
};
