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
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('locations')->nullOnDelete();
            $table->string('type', 32)->index();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->string('search_index', 512)->nullable();
            $table->char('country_code', 2)->nullable()->index();
            $table->string('region_code', 32)->nullable();
            $table->string('city_name')->nullable();
            $table->string('address')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();
            $table->string('timezone')->nullable();
            $table->string('osm_id')->nullable();
            $table->string('osm_type', 16)->nullable();
            $table->string('full_slug')->unique();
            $table->unsignedTinyInteger('depth')->default(0);
            $table->json('metadata')->nullable();
            $table->boolean('is_verified')->default(false);
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('content_count')->default(0);
            $table->boolean('indexable')->default(false);
            $table->timestamp('indexable_updated_at')->nullable();
            $table->timestamps();

            // Idempotent external identity (NULLs are distinct, so many manual
            // rows may coexist without an osm identity).
            $table->unique(['osm_type', 'osm_id']);

            $table->index(['parent_id', 'type']);
            $table->index(['type', 'country_code']);
            $table->index(['latitude', 'longitude']);
            $table->index('indexable');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
