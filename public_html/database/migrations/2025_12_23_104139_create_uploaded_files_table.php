<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('uploaded_files', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto increment

            $table->enum('disk', ['local', 'public', 's3'])->nullable();

            $table->string('path');

            $table->string('original_name')->nullable();
            $table->string('mime')->nullable();

            // size in bytes (stored as varchar as per your schema)
            $table->string('size')->nullable();

            $table->unsignedBigInteger('user_id')->nullable();

            $table->softDeletes(); // deleted_at

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('uploaded_files');
    }
};
