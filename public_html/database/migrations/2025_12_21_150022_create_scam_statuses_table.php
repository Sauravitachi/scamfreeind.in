<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scam_statuses', function (Blueprint $table) {
            $table->id();

            $table->unsignedInteger('index');

            $table->string('slug')->unique();
            $table->string('title');

            $table->enum('type', [
                'sales',
                'drafting',
                'service',
            ])->index();

            $table->unsignedInteger('notify_after_days')->nullable();
            $table->integer('remainder_after_hours')->nullable();

            $table->unsignedBigInteger('customer_enquiry_notify_role_id')->nullable();

            $table->unsignedSmallInteger('cap_scams')->nullable();
            $table->unsignedSmallInteger('cap_last_days')->nullable();

            $table->boolean('is_file_required')->default(false);
            $table->boolean('is_data_update_required')->default(false);
            $table->boolean('is_scam_type_update_required')->default(false);
            $table->boolean('is_lock')->default(false);
            $table->boolean('is_approval_required')->default(false);
            $table->boolean('bypass_enquiry')->default(false);
            $table->boolean('is_closed')->default(false);
            $table->boolean('is_freezable')->default(false);
            $table->boolean('unassign_scam')->default(false);

            $table->unsignedSmallInteger('hours_to_freeze')->nullable();
            $table->unsignedSmallInteger('freeze_scams_threshold')->nullable();
            $table->unsignedSmallInteger('freeze_release_scams_threshold')->nullable();
            $table->unsignedSmallInteger('unassign_scam_in_days')->nullable();

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (important for workflow performance)
            |--------------------------------------------------------------------------
            */
            $table->index('index');
            $table->index('is_closed');
            $table->index('is_lock');
            $table->index('is_freezable');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_statuses');
    }
};
