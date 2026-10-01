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
        Schema::create('location_import_batches', function (Blueprint $table) {
            $table->id();
            $table->string('source', 32);
            $table->string('dataset_version')->nullable();
            $table->string('checksum')->nullable();
            $table->string('status', 16)->default('pending')->index();
            $table->json('countries')->nullable();
            $table->unsignedBigInteger('total')->default(0);
            $table->unsignedBigInteger('processed')->default(0);
            $table->unsignedBigInteger('created')->default(0);
            $table->unsignedBigInteger('updated')->default(0);
            $table->unsignedBigInteger('skipped')->default(0);
            $table->text('error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('finished_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('location_import_batches');
    }
};
