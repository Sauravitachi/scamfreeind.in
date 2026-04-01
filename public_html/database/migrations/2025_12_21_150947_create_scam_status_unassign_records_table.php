<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scam_status_unassign_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('scam_id');

            $table->unsignedBigInteger('assignee_id')->nullable();

            $table->unsignedBigInteger('status_id')->nullable();

            $table->unsignedBigInteger('enquiry_status_id')->nullable();

            $table->enum('status_type', [
                'sales',
                'drafting',
                'service',
            ]);

            $table->timestamp('created_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (important for audit & performance)
            |--------------------------------------------------------------------------
            */
            $table->index('scam_id');
            $table->index('assignee_id');
            $table->index('status_id');
            $table->index('enquiry_status_id');
            $table->index('status_type');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_status_unassign_records');
    }
};
