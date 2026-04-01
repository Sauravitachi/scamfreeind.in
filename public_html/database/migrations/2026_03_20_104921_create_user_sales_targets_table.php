<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('user_sales_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            // Define the timeframe (e.g., 'monthly', 'weekly')
            $table->string('period_type')->default('monthly'); 
            $table->date('starts_at');
            $table->date('ends_at');

            // Target metrics
            $table->decimal('target_amount', 15, 2)->default(0);
            $table->integer('target_points')->default(0);
            $table->integer('target_case_count')->default(0);

            $table->timestamps();
            
            // Ensure one target per user per period
            $table->unique(['user_id', 'starts_at', 'ends_at'], 'user_period_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_sales_targets');
    }
};
