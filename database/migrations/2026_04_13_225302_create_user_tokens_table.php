<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_tokens', function (Blueprint $table) {
            $table->id();
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
            $table->foreignId('item_id')->constrained('flexibility_items')->onDelete('cascade'); // Relasi ke item toko
            $table->enum('status', ['AVAILABLE', 'USED', 'EXPIRED'])->default('AVAILABLE');
            
            // Relasi opsional: Token ini dipakai saat absensi yang mana?
            $table->unsignedBigInteger('used_at_attendance_id')->nullable();
            $table->foreign('used_at_attendance_id')->references('id')->on('absensi')->onDelete('set null');
            
            $table->timestamp('used_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_tokens');
    }
};