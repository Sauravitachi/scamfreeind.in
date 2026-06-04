<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasColumn('lawyer_leads', 'lawyer_id')) {
            Schema::table('lawyer_leads', function (Blueprint $table) {
                $table->foreignId('lawyer_id')->nullable()->after('problem_type_id')->constrained('lawyers')->onDelete('set null');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('lawyer_leads', 'lawyer_id')) {
            Schema::table('lawyer_leads', function (Blueprint $table) {
                $table->dropForeign(['lawyer_id']);
                $table->dropColumn('lawyer_id');
            });
        }
    }
};
