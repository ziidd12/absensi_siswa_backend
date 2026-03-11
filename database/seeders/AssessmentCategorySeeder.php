<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssessmentCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Terlambat Masuk', 'type' => 'pelanggaran', 'default_score' => 10],
            ['name' => 'Atribut Tidak Lengkap', 'type' => 'pelanggaran', 'default_score' => 5],
            ['name' => 'Bolos Pelajaran', 'type' => 'pelanggaran', 'default_score' => 20],
            ['name' => 'Berkata Kasar', 'type' => 'pelanggaran', 'default_score' => 15],
            ['name' => 'Merusak Fasilitas', 'type' => 'pelanggaran', 'default_score' => 50],
            ['name' => 'Juara Lomba', 'type' => 'prestasi', 'default_score' => 50],
            ['name' => 'Membantu Guru', 'type' => 'prestasi', 'default_score' => 10],
            ['name' => 'Aktif Bertanya', 'type' => 'prestasi', 'default_score' => 5],
            ['name' => 'Jujur & Amanah', 'type' => 'prestasi', 'default_score' => 30],
        ];

        foreach ($categories as $category) {
            DB::table('assessment_categories')->updateOrInsert(
                ['name' => $category['name']], // Supaya nggak duplikat kalau dijalankan dua kali
                [
                    'type' => $category['type'],
                    'default_score' => $category['default_score'],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }
}