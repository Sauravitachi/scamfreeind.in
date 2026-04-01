<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement("ALTER TABLE scam_assignee_records MODIFY COLUMN assignee_type ENUM('sales', 'drafting', 'service', 'sub_admin') NOT NULL");
        DB::statement("ALTER TABLE scam_activities MODIFY COLUMN event ENUM('created', 'updated', 'sales_assign', 'drafting_assign', 'service_assign', 'sub_admin_assign', 'sales_status', 'drafting_status', 'recycled') NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Note: Reverting might fail if there are 'sub_admin' records.
        DB::statement("ALTER TABLE scam_assignee_records MODIFY COLUMN assignee_type ENUM('sales', 'drafting', 'service') NOT NULL");
        DB::statement("ALTER TABLE scam_activities MODIFY COLUMN event ENUM('created', 'updated', 'sales_assign', 'drafting_assign', 'service_assign', 'sales_status', 'drafting_status', 'recycled') NOT NULL");
    }
};
