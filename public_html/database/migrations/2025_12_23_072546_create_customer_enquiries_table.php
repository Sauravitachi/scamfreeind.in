<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_enquiries', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto increment

            $table->unsignedBigInteger('customer_id')->nullable();

            $table->text('message')->nullable();

            $table->unsignedBigInteger('scam_source_id')->nullable();

            $table->unsignedBigInteger('sales_status_id')->nullable();
            $table->unsignedBigInteger('drafting_status_id')->nullable();

            $table->timestamp('sales_status_updated_at')->nullable();
            $table->timestamp('drafting_status_updated_at')->nullable();

            $table->string('remark', 1000)->nullable();

            $table->unsignedSmallInteger('occurrence')->default(0);

            $table->timestamp('manually_assigned_at')->nullable();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_enquiries');
    }
};
