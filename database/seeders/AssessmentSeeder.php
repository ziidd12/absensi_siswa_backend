<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AssessmentSeeder extends Seeder
{
    public function run()
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // 1. Pakai updateOrInsert biar tidak error "Duplicate Entry"
        DB::table('tahun_ajaran')->updateOrInsert(
            ['tahun' => '2025/2026', 'semester' => 'Ganjil'],
            ['is_active' => 1, 'created_at' => now()]
        );
        
        // Ambil ID-nya setelah dipastikan ada
        $tahunId = DB::table('tahun_ajaran')
            ->where('tahun', '2025/2026')
            ->where('semester', 'Ganjil')
            ->value('id');

        // 2. Isi Kelas (Gunakan updateOrInsert juga biar aman)
        DB::table('kelas')->updateOrInsert(
            ['tingkat' => 12, 'jurusan' => 'RPL', 'nomor_kelas' => '1'],
            ['tahun_ajaran_id' => $tahunId, 'created_at' => now()]
        );
        
        $kelasId = DB::table('kelas')
            ->where('tingkat', 12)
            ->where('jurusan', 'RPL')
            ->value('id');

        // 3. Buat User dummy
        $userId1 = DB::table('users')->insertGetId([
            'name' => 'Budi Santoso',
            'email' => 'budi'.time().'@example.com', // Pakai time() biar emailnya gak duplikat
            'password' => Hash::make('password'),
            'created_at' => now(),
        ]);

        // 4. Isi Siswa
        DB::table('siswa')->updateOrInsert(
            ['NIS' => '12345'],
            [
                'nama_siswa' => 'Budi Santoso', 
                'id_kelas' => $kelasId,
                'user_id' => $userId1,
                'created_at' => now()
            ]
        );

        // 5. Isi Kategori Penilaian
        $categories = ['Kerapihan', 'Kedisiplinan', 'Sopan Santun'];
        foreach ($categories as $cat) {
            DB::table('assessment_categories')->updateOrInsert(
                ['name' => $cat],
                ['created_at' => now()]
            );
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
    }
}