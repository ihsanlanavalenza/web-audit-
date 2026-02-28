<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('data_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')->constrained()->onDelete('cascade');
            $table->foreignId('kap_id')->constrained('kap_profiles')->onDelete('cascade');
            $table->integer('no')->default(0);
            $table->string('section')->nullable();
            $table->string('account_process')->nullable();
            $table->text('description')->nullable();
            $table->date('request_date')->nullable();
            $table->date('expected_received')->nullable();
            $table->string('input_file')->nullable();
            $table->enum('status', [
                'partially_received',
                'on_review',
                'received',
                'not_applicable',
                'pending',
            ])->default('pending');
            $table->text('comment_client')->nullable();
            $table->text('comment_auditor')->nullable();
            $table->date('date_input')->nullable();
            $table->timestamp('last_update')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('data_requests');
    }
};
