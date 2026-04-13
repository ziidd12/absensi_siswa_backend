<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('siswa', function (Blueprint $table) {
        // Kita tambah kolom points_store, default-nya 0
        $table->integer('points_store')->default(0)->after('nis'); 
    });
}

public function down()
{
    Schema::table('siswa', function (Blueprint $table) {
        $table->dropColumn('points_store');
    });
}
};
