<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Guru;
use App\Models\Siswa;
use App\Models\Kelas;
use App\Models\Mapel;
use App\Models\Jadwal;
use App\Models\Sesi;
use App\Models\TahunAjaran;
use App\Models\Absensi;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat User Admin
        User::create([
            'name' => 'Administrator Sistem',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
        ]);

        // 2. Tahun Ajaran
        $ta = TahunAjaran::create([
            'tahun' => '2025/2026',
            'semester' => 'Genap',
            'is_active' => true
        ]);

        // 3. Kelas
        $kelas = Kelas::create([
            'tingkat' => 12,
            'jurusan' => 'RPL',
            'nomor_kelas' => '1'
        ]);

        // 4. Mapel
        $mapel = Mapel::create(['nama_mapel' => 'Pemrograman Laravel']);

        // 5. Guru (User & Profil Guru)
        $uGuru = User::create([
            'name' => 'Budi Guru, S.Kom',
            'email' => 'guru@test.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);
        
        // Simpan data guru ke variabel agar ID-nya bisa dipakai di Jadwal
        $guruProfile = Guru::create([
            'user_id' => $uGuru->id,
            'nama_guru' => 'Budi Guru, S.Kom',
            'NIP' => '198801012023011001'
        ]);

        // 6. Siswa (User & Profil Siswa)
        $uSiswa = User::create([
            'name' => 'Ahmad Siswa',
            'email' => 'siswa@test.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
        ]);

        $siswaProfile = Siswa::create([
            'user_id' => $uSiswa->id,
            'id_kelas' => $kelas->id,
            'nama_siswa' => 'Ahmad Siswa',
            'NIS' => '222310101'
        ]);

        // 7. Jadwal (Senin - Minggu)
        $haris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $jadwal_ids = [];

        foreach ($haris as $hari) {
            $j = Jadwal::create([
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'guru_id' => $guruProfile->id, // PERBAIKAN: Mengacu ke id tabel guru
                'hari' => $hari,
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:30:00',
            ]);
            $jadwal_ids[$hari] = $j->id;
        }

        // 8. Sesi QR & Absensi Otomatis
        // Menggunakan tanggal saat ini (Maret 2026) sesuai konteks sistem
        $start = Carbon::create(2026, 3, 1); 
        
        for ($i = 0; $i <= 7; $i++) {
            $tgl = $start->copy()->addDays($i);
            $hariIndo = $this->getHariIndo($tgl->format('l'));

            $sesi = Sesi::create([
                'jadwal_id' => $jadwal_ids[$hariIndo],
                'tanggal' => $tgl->format('Y-m-d'),
                'token_qr' => Str::random(32),
            ]);

            // Jika tanggal sudah lewat atau hari ini, buat data absensi dummy
            if ($tgl->isPast() || $tgl->isToday()) {
                Absensi::create([
                    'siswa_id' => $siswaProfile->id,
                    'sesi_id' => $sesi->id,
                    'tahun_ajaran_id' => $ta->id,
                    'waktu_scan' => $tgl->copy()->hour(7)->minute(rand(30, 45)),
                    'status' => 'Hadir'
                ]);
            }
        }
    }

    private function getHariIndo($day)
    {
        $map = [
            'Monday' => 'Senin', 'Tuesday' => 'Selasa', 'Wednesday' => 'Rabu',
            'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu', 'Sunday' => 'Minggu'
        ];
        return $map[$day];
    }
}