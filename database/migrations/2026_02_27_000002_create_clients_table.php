<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kap_id')->constrained('kap_profiles')->onDelete('cascade');
            $table->string('nama_client');
            $table->string('nama_pic');
            $table->string('no_contact');
            $table->year('tahun_audit');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};
