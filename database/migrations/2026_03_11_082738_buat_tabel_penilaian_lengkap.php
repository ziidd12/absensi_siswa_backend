<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // 1. Tabel Kategori Penilaian (Indikator)
        Schema::create('assessment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name'); 
            $table->text('description')->nullable(); // Untuk info tambahan indikator
            $table->timestamps();
        });

        // 2. Tabel Utama Penilaian (Siapa menilai siapa)
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            // Guru yang menilai
            $table->foreignId('evaluator_id')->constrained('users')->onDelete('cascade'); 
            // Siswa yang dinilai
            $table->foreignId('tahun_ajaran_id');
            $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade'); 
            $table->text('general_notes')->nullable(); // Tempat simpan "Baju Keluar", dll
            $table->timestamps();
        });

        // 3. Tabel Detail Skor (Nilai per kategori)
        Schema::create('assessment_details', function (Blueprint $table) {
            $table->id();
            // Jika data penilaian dihapus, detail otomatis ikut hapus
            $table->foreignId('assessment_id')->constrained('assessments')->onDelete('cascade');
            $table->foreignId('category_id')->constrained('assessment_categories');
            $table->integer('score'); 
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('assessment_details');
        Schema::dropIfExists('assessments');
        Schema::dropIfExists('assessment_categories');
    }
};