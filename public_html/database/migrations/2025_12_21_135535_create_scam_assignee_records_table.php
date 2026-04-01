<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scam_assignee_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('scam_id');

            $table->unsignedBigInteger('assignee_id')->nullable();

            $table->enum('assignee_type', [
                'sales',
                'drafting',
                'service',
            ]);

            $table->unsignedBigInteger('unassign_status_id')->nullable();

            $table->unsignedBigInteger('causer_id')->nullable();

            $table->timestamp('created_at')->nullable();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (recommended for performance)
            |--------------------------------------------------------------------------
            */
            $table->index('scam_id');
            $table->index('assignee_id');
            $table->index('assignee_type');
            $table->index('unassign_status_id');
            $table->index('causer_id');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_assignee_records');
    }
};
