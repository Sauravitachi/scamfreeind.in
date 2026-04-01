<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('otps', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->string('phone_number', 255);
            $table->unsignedBigInteger('customer_id')->nullable();

            $table->string('otp', 12);
            $table->string('session_id', 255);

            $table->timestamp('expire_at')->nullable();

            $table->string('ip_address', 255)->nullable();
            $table->string('user_agent', 255)->nullable();

            $table->text('response_body')->nullable();

            $table->timestamp('used_at')->nullable();

            $table->timestamps(); // created_at & updated_at
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('otps');
    }
};
