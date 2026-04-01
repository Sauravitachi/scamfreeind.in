<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scam_files', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto increment

            $table->unsignedBigInteger('scam_id');
            $table->unsignedBigInteger('file_id');

            $table->string('message', 1000)->nullable();

            $table->unsignedBigInteger('user_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_files');
    }
};
