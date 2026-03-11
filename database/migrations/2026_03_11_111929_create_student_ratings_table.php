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
        Schema::create('student_ratings', function (Blueprint $table) {
        $table->id();
        $table->integer('siswa_id');
        $table->integer('guru_id')->default(1);
        $table->decimal('kedisiplinan', 8, 2);
        $table->decimal('kerja_sama', 8, 2);
        $table->decimal('tanggung_jawab', 8, 2);
        $table->decimal('inisiatif', 8, 2);
        $table->text('catatan')->nullable();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('student_ratings');
    }
};