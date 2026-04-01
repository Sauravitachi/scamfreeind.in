<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scam_activities', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('scam_id');

            $table->unsignedBigInteger('user_id')->nullable();

            $table->enum('event', [
                'created',
                'updated',
                'sales_assign',
                'drafting_assign',
                'service_assign',
                'sales_status',
                'drafting_status',
                'recycled',
            ]);

            $table->string('description', 1000);

            $table->timestamp('notify_at')->nullable();

            $table->timestamps();

            /* ================= INDEXES ================= */
            $table->index('scam_id');
            $table->index('user_id');
            $table->index('event');
            $table->index('notify_at');

            /* ================= FOREIGN KEYS ================= */
            $table->foreign('scam_id')
                ->references('id')
                ->on('scams')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_activities');
    }
};
