<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();

            $table->string('track_id', 30)->nullable();

            $table->string('first_name')->nullable();
            $table->string('last_name')->nullable();

            $table->string('scammer_name')->nullable();

            $table->boolean('check_box_value')->default(false);
            $table->timestamp('check_box_marked_at')->nullable();

            $table->enum('gender', ['male', 'female', 'other'])->nullable();

            $table->unsignedBigInteger('state')->nullable();

            $table->string('email')->nullable();

            $table->string('country_code', 10)->nullable();
            $table->string('dial_code', 10)->nullable();

            $table->string('phone_number', 15);

            $table->timestamps();

            /* ================= INDEXES ================= */
            $table->index('track_id');
            $table->index('phone_number');
            $table->index('email');
            $table->index('state');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
