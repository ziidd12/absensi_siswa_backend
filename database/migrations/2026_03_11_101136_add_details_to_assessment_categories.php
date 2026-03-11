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
    Schema::table('assessment_categories', function (Blueprint $table) {
        // Tambah kolom type dan default_score
        $table->enum('type', ['pelanggaran', 'prestasi'])->default('pelanggaran')->after('name');
        $table->integer('default_score')->default(0)->after('type');
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('assessment_categories', function (Blueprint $table) {
            //
        });
    }
};
