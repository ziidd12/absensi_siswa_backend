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
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('evaluator_id'); 
            $table->unsignedBigInteger('evaluatee_id');
            $table->unsignedBigInteger('tahun_ajaran_id'); 
            
            $table->date('assessment_date');
            $table->text('general_notes')->nullable(); 
            
            $table->timestamps();
            $table->foreign('evaluator_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('evaluatee_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('tahun_ajaran_id')->references('id')->on('tahun_ajaran')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assessment');
    }
};
