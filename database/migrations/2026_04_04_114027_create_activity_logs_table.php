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
        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // who did it
            $table->string('model_type'); // e.g. DataRequest, Client
            $table->unsignedBigInteger('model_id');
            $table->string('action'); // created, updated, deleted, status_changed
            $table->text('description')->nullable();
            $table->json('old_payload')->nullable(); // old state
            $table->json('new_payload')->nullable(); // new state
            $table->timestamps();

            $table->index(['model_type', 'model_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
