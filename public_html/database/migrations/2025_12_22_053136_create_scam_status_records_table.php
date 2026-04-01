<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scam_status_records', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('scam_id');
            $table->unsignedBigInteger('status_id')->nullable();

            $table->enum('status_type', ['drafting', 'sales', 'service']);

            $table->string('status_remark', 1000)->nullable();

            $table->timestamp('status_notify_at')->nullable();
            $table->timestamp('status_notification_acknowledged_at')->nullable();

            $table->unsignedBigInteger('causer_id');

            $table->enum('review', ['pending', 'rejected', 'approved'])->nullable();
            $table->unsignedBigInteger('review_resolver_id')->nullable();
            $table->timestamp('review_resolved_at')->nullable();
            $table->string('review_resolve_remark', 2000)->nullable();

            $table->timestamps();

            /* ================= INDEXES ================= */
            $table->index('scam_id');
            $table->index('status_type');
            $table->index('causer_id');
            $table->index('review');

            /* ================= FOREIGN KEYS ================= */
            $table->foreign('scam_id')
                ->references('id')
                ->on('scams')
                ->cascadeOnDelete();

            $table->foreign('status_id')
                ->references('id')
                ->on('scam_statuses')
                ->nullOnDelete();

            $table->foreign('causer_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            $table->foreign('review_resolver_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_status_records');
    }
};
