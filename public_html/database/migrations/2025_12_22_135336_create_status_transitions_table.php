<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('status_transitions', function (Blueprint $table) {
            $table->id();

            $table->enum('type', ['sales', 'drafting', 'service']);

            $table->unsignedBigInteger('current_status_id');
            $table->unsignedBigInteger('next_status_id');

            $table->timestamps();

            /* ================= INDEXES ================= */
            $table->index(['type', 'current_status_id']);
            $table->index('next_status_id');

            /* ================= FOREIGN KEYS ================= */
            $table->foreign('current_status_id')
                ->references('id')
                ->on('scam_statuses')
                ->cascadeOnDelete();

            $table->foreign('next_status_id')
                ->references('id')
                ->on('scam_statuses')
                ->cascadeOnDelete();

            /* ================= UNIQUE ================= */
            $table->unique(
                ['type', 'current_status_id', 'next_status_id'],
                'status_transition_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_transitions');
    }
};
