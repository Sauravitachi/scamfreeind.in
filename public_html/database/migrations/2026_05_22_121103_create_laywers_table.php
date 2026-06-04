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
        Schema::create('lawyers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone', 20)->nullable();
            $table->string('address')->nullable();
            $table->string('image')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::create('lawyer_specializations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lawyer_id')->constrained('lawyers')->onDelete('cascade');
            $table->foreignId('problem_type_id')->constrained('problem_types')->onDelete('cascade');
            $table->timestamps();

            $table->unique(['lawyer_id', 'problem_type_id'], 'lawyer_spec_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lawyer_specializations');
        Schema::dropIfExists('lawyers');
    }
};
