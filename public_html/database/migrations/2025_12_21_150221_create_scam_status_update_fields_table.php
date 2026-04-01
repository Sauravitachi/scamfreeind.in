<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scam_status_update_fields', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('scam_status_id');

            $table->enum('status_field_type', [
                'status_remark',
                'first_name',
                'last_name',
                'scammer_name',
                'gender',
                'state',
                'email',
                'scam_amount',
                'registration_amount',
                'claim_amount',
                'recovery_amount',
                'scam_type',
                'file_upload',
                'status_notify_at',
                'check_box_value',
            ]);

            $table->boolean('is_required')->default(false);
            $table->boolean('prefill_previous_value')->default(false);

            $table->unsignedSmallInteger('order')->default(0);

            $table->timestamps();

            /*
            |--------------------------------------------------------------------------
            | INDEXES (workflow + UI performance)
            |--------------------------------------------------------------------------
            */
            $table->index('scam_status_id');
            $table->index('status_field_type');
            $table->index('order');
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_status_update_fields');
    }
};
