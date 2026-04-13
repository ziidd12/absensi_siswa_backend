<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('point_rules', function (Blueprint $table) {
            $table->id();
            $table->string('rule_name'); // Contoh: "Tepat Waktu"
            $table->string('target_role'); // Contoh: "siswa" atau "guru"
            $table->enum('condition_operator', ['<', '>', 'BETWEEN', '=']);
            $table->string('condition_value'); // Disimpan string agar fleksibel (Waktu/Menit)
            $table->integer('point_modifier'); // +5 atau -3
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('point_rules');
    }
};