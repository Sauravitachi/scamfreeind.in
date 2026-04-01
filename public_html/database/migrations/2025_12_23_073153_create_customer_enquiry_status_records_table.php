<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customer_enquiry_status_records', function (Blueprint $table) {
            $table->id(); // bigint unsigned auto increment

            $table->unsignedBigInteger('customer_enquiry_id');

            $table->unsignedBigInteger('status_id')->nullable();

            $table->enum('status_type', ['drafting', 'sales']);

            $table->string('remark', 1000)->nullable();

            $table->unsignedBigInteger('causer_id');

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customer_enquiry_status_records');
    }
};
