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
    Schema::create('redeems', function (Blueprint $table) {
        $table->id();
        $table->foreignId('siswa_id')->constrained('siswa')->onDelete('cascade');
        $table->foreignId('store_item_id')->constrained('store_items')->onDelete('cascade');
        $table->integer('poin_dikeluarkan');
        $table->string('status')->default('pending'); // pending, aktif, used
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redeems');
    }
};
