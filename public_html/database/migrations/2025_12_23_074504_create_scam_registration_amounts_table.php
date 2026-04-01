<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scam_registration_amounts', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto increment

            $table->string('title');

            $table->decimal('amount', 28, 2)->default(0.00);

            $table->decimal('points', 28, 2)->nullable();

            $table->string('description', 1000)->nullable();

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_registration_amounts');
    }
};
