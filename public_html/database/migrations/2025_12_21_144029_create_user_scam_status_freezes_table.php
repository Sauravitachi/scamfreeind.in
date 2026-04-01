<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('user_scam_status_freezes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->unsignedBigInteger('status_id')->nullable();

            $table->enum('status_type', [
                'sales',
                'drafting',
                'service',
            ]);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (important for performance)
            |--------------------------------------------------------------------------
            */
            $table->index('user_id');
            $table->index('status_id');
            $table->index('status_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_scam_status_freezes');
    }
};
