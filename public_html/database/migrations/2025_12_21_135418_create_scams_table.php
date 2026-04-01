<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scams', function (Blueprint $table) {
            $table->id();

            $table->string('sales_lead_type')->nullable();
            $table->string('track_id', 30)->nullable()->unique();
            $table->text('mark')->nullable();

            $table->unsignedBigInteger('customer_id');
            $table->unsignedBigInteger('scam_type_id')->nullable();
            $table->unsignedBigInteger('scam_source_id')->nullable();

            $table->decimal('scam_amount', 28, 2)->nullable();
            $table->decimal('claim_amount', 10, 2)->nullable();
            $table->decimal('recovery_amount', 12, 2)->nullable();

            $table->text('customer_description')->nullable();

            // Sales
            $table->unsignedBigInteger('sales_assignee_id')->nullable();
            $table->unsignedBigInteger('sales_status_id')->nullable();
            $table->timestamp('sales_assigned_at')->nullable();
            $table->timestamp('sales_status_updated_at')->nullable();

            // Drafting
            $table->unsignedBigInteger('drafting_assignee_id')->nullable();
            $table->unsignedBigInteger('drafting_status_id')->nullable();
            $table->timestamp('drafting_assigned_at')->nullable();
            $table->timestamp('drafting_status_updated_at')->nullable();

            // Service
            $table->unsignedBigInteger('service_assignee_id')->nullable();
            $table->unsignedBigInteger('service_status_id')->nullable();
            $table->timestamp('service_assigned_at')->nullable();

            // Sub Admin
            $table->unsignedBigInteger('sub_admin_id')->nullable();
            $table->dateTime('sub_admin_assigned_at')->nullable();

            // Status Records
            $table->unsignedBigInteger('sales_status_record_id')->nullable();
            $table->unsignedBigInteger('drafting_status_record_id')->nullable();
            $table->unsignedBigInteger('service_status_record_id')->nullable();

            // Latest Unassign Records
            $table->unsignedBigInteger('latest_sales_status_unassign_record_id')->nullable();
            $table->unsignedBigInteger('latest_drafting_status_unassign_record_id')->nullable();
            $table->unsignedBigInteger('latest_service_status_unassign_record_id')->nullable();

            // Recycle / Duplicate
            $table->boolean('is_duplicate')->default(false)->index();
            $table->unsignedBigInteger('recycled_parent_scam_id')->nullable();
            $table->timestamp('recycled_at')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES
            |--------------------------------------------------------------------------
            */
            $table->index('customer_id');
            $table->index('scam_type_id');
            $table->index('scam_source_id');

            $table->index('sales_assignee_id');
            $table->index('sales_status_id');

            $table->index('drafting_assignee_id');
            $table->index('drafting_status_id');

            $table->index('service_assignee_id');
            $table->index('service_status_id');

            $table->index('sales_status_record_id');
            $table->index('drafting_status_record_id');
            $table->index('service_status_record_id');

            $table->index('latest_sales_status_unassign_record_id');
            $table->index('latest_drafting_status_unassign_record_id');
            $table->index('latest_service_status_unassign_record_id');

            $table->index('recycled_parent_scam_id');
            $table->index('recycled_at');
            $table->index('created_at');
            $table->index('sub_admin_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scams');
    }
};
