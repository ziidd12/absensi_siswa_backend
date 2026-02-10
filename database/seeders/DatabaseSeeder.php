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
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Tahun Ajaran (Sesuai migrasi baru: tahun, semester, is_active)
        $ta = TahunAjaran::create([
            'tahun' => '2025/2026',
            'semester' => 'Genap',
            'is_active' => true
        ]);

        // 2. Kelas
        $kelas = Kelas::create(['nama_kelas' => 'XII RPL 1']);

        // 3. Mapel
        $mapel = Mapel::create(['nama_mapel' => 'Pemrograman Laravel']);

        // 4. Guru (User + Guru)
        $uGuru = User::create([
            'name' => 'Budi Guru, S.Kom',
            'email' => 'guru@test.com',
            'password' => Hash::make('password'),
            'role' => 'guru',
        ]);
        
        Guru::create([
            'user_id' => $uGuru->id,
            'nama_guru' => 'Budi Guru, S.Kom',
            'NIP' => '198801012023011001' // Pakai NIP sesuai migrasi
        ]);

        // 5. Siswa (User + Siswa)
        $uSiswa = User::create([
            'name' => 'Ahmad Siswa',
            'email' => 'siswa@test.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
            // 'device_id' => 'DEV-123456' 
        ]);

        $siswa = Siswa::create([
            'user_id' => $uSiswa->id,
            'id_kelas' => $kelas->id, // Sesuaikan dengan id_kelas di migrasi
            'nama_siswa' => 'Ahmad Siswa',
            'NIS' => '222310101' // Pakai NIS sesuai migrasi
        ]);

        // 6. Jadwal (Senin - Minggu)
        $haris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        $jadwal_ids = [];

        foreach ($haris as $hari) {
            $j = Jadwal::create([
                'kelas_id' => $kelas->id,
                'mapel_id' => $mapel->id,
                'guru_id' => $uGuru->id, // Asumsi jadwal pakai id guru/user
                'hari' => $hari,
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:30:00',
            ]);
            $jadwal_ids[$hari] = $j->id;
        }

        // 7. Sesi QR & Absensi (9 Feb - 16 Feb 2026)
        $start = Carbon::create(2026, 2, 9);
        
        for ($i = 0; $i <= 7; $i++) {
            $tgl = $start->copy()->addDays($i);
            $hariIndo = $this->getHariIndo($tgl->format('l'));

            $sesi = Sesi::create([
                'jadwal_id' => $jadwal_ids[$hariIndo],
                'tanggal' => $tgl->format('Y-m-d'),
                'token_qr' => Str::random(32),
            ]);

            // Optional: Buat 1 data absen dummy untuk testing history
            if ($tgl->isPast() || $tgl->isToday()) {
                \App\Models\Absensi::create([
                    'siswa_id' => $siswa->id,
                    'sesi_id' => $sesi->id,
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