<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('scam_leads', function (Blueprint $table) {
            $table->id();

            $table->string('name')->nullable();
            $table->string('email')->nullable();

            $table->string('country_code', 10)->nullable();
            $table->string('dial_code', 10)->nullable();
            $table->string('phone_number', 15);

            $table->decimal('scam_amount', 28, 2)->nullable();

            $table->unsignedBigInteger('scam_type_id')->nullable();

            $table->text('customer_description')->nullable();

            $table->unsignedBigInteger('scam_source_id')->nullable();
            $table->unsignedBigInteger('sub_admin_id')->nullable();
            $table->unsignedBigInteger('existing_customer_id')->nullable();

            $table->boolean('is_duplicate')->default(false);

            $table->longText('errors')->nullable();

            $table->unsignedSmallInteger('count')->default(0);

            $table->string('page_title')->nullable();
            $table->text('page_url')->nullable();

            $table->string('ip_address', 45)->nullable();
            $table->string('ip_country', 100)->nullable();
            $table->string('ip_state', 100)->nullable();
            $table->string('ip_city', 100)->nullable();

            $table->timestamp('selected_at')->nullable();

            $table->timestamps();

            /* ================= INDEXES ================= */
            $table->index('phone_number');
            $table->index('email');
            $table->index('is_duplicate');
            $table->index('scam_type_id');
            $table->index('scam_source_id');
            $table->index('sub_admin_id');
            $table->index('existing_customer_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scam_leads');
    }
};
