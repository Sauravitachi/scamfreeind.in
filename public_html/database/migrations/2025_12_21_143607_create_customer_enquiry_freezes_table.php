<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customer_enquiry_freezes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');

            $table->enum('status_type', [
                'sales',
                'drafting',
                'service',
            ]);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (performance & consistency)
            |--------------------------------------------------------------------------
            */
            $table->index('user_id');
            $table->index('status_type');
            $table->index('created_at');

            // Prevent duplicate freezes for same user & stage
            $table->unique(['user_id', 'status_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_enquiry_freezes');
    }
};
