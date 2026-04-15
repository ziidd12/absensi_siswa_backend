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
    Schema::create('poin_histories', function (Blueprint $table) {
        $table->id();
        $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
        $table->foreignId('store_item_id')->nullable()->constrained('store_items')->onDelete('set null'); // <--- TAMBAHKEUN IEU
        $table->integer('poin_perubahan');
        $table->string('keterangan');
        $table->string('status')->default('aktif'); // <--- TAMBAHKEUN IEU
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poin_histories');
    }
};
