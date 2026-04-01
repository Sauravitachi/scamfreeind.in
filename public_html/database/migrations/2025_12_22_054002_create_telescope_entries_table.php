<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telescope_entries', function (Blueprint $table) {
            $table->bigIncrements('sequence');

            $table->uuid('uuid');
            $table->uuid('batch_id');

            $table->string('family_hash')->nullable();
            $table->boolean('should_display_on_index')->default(true);

            $table->string('type', 20);
            $table->longText('content');

            $table->dateTime('created_at')->nullable();

            /* ================= INDEXES ================= */
            $table->index('uuid');
            $table->index('batch_id');
            $table->index('family_hash');
            $table->index('type');
            $table->index('should_display_on_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telescope_entries');
    }
};
