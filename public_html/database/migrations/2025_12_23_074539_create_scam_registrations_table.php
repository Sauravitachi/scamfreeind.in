<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scam_registrations', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto increment

            $table->unsignedBigInteger('scam_id');

            $table->unsignedBigInteger('scam_assigned_id')->nullable();

            $table->unsignedBigInteger('scam_registration_amount_id');

            $table->unsignedBigInteger('causer_id')->nullable();

            $table->timestamp('caused_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_registrations');
    }
};
