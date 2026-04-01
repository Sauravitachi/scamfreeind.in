<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto increment

            $table->string('name');
            $table->string('username')->unique();
            $table->string('email')->unique();

            $table->string('country_code', 10)->nullable();
            $table->string('dial_code', 10)->nullable();
            $table->string('phone_number', 30);

            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');

            $table->string('avatar')->nullable();
            $table->unsignedBigInteger('profile_picture_id')->nullable();

            $table->boolean('status')->default(1); // 1 = Active, 0 = InActive
            $table->boolean('is_excluded')->default(0);

            $table->rememberToken();

            $table->timestamp('last_pinged_at')->nullable();
            $table->timestamp('freeze_disabled_until')->nullable();
            $table->timestamp('login_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
