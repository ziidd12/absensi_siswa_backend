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
    Schema::create('store_items', function (Blueprint $table) {
        $table->id();
        $table->string('nama_item');
        $table->integer('harga_poin');
        $table->string('icon')->default('fastfood'); // Nama icon Material Design
        $table->string('warna')->default('orange'); // Warna tema item
        $table->integer('stok')->default(0);
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('store_items');
    }
};
