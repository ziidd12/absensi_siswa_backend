<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Jalankan migrasi untuk menambahkan kolom tahun_ajaran_id.
     */
    public function up(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            $table->foreignId('tahun_ajaran_id')
                ->after('nomor_kelas')
                ->nullable() // <--- WAJIB TAMBAHKAN INI agar data lama tidak error
                ->constrained('tahun_ajaran')
                ->onDelete('cascade');
        });
    }

    /**
     * Batalkan migrasi dengan menghapus foreign key dan kolomnya.
     */
    public function down(): void
    {
        Schema::table('kelas', function (Blueprint $table) {
            // Hapus constraint foreign key dulu, baru hapus kolomnya
            $table->dropForeign(['tahun_ajaran_id']);
            $table->dropColumn('tahun_ajaran_id');
        });
    }
};