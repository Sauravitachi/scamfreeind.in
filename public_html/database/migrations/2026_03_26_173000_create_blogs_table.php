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
        Schema::create('blogs', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('title');
            $blueprint->string('slug')->unique();
            $blueprint->text('summary')->nullable();
            $blueprint->longText('content');
            $blueprint->string('featured_image')->nullable();
            $blueprint->foreignId('author_id')->constrained('users')->cascadeOnDelete();
            $blueprint->enum('status', ['draft', 'published', 'scheduled'])->default('draft');
            $blueprint->timestamp('published_at')->nullable();
            $blueprint->string('meta_title')->nullable();
            $blueprint->text('meta_description')->nullable();
            $blueprint->string('meta_keywords')->nullable();
            $blueprint->boolean('is_featured')->default(false);
            $blueprint->timestamps();
            $blueprint->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};
