<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('telescope_entries_tags', function (Blueprint $table) {

            $table->uuid('entry_uuid');
            $table->string('tag');

            /* ================= INDEXES ================= */
            $table->index('entry_uuid');
            $table->index('tag');

            /* ================= FOREIGN KEY ================= */
            $table->foreign('entry_uuid')
                ->references('uuid')
                ->on('telescope_entries')
                ->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('telescope_entries_tags');
    }
};
