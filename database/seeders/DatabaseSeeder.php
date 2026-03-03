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
        // 1. Tahun Ajaran
        $ta = TahunAjaran::create([
            'tahun' => '2025/2026',
            'semester' => 'Genap',
            'is_active' => true
        ]);

        // 2. Kelas
        $kelas = Kelas::create([
            'tingkat' => 12,
            'jurusan' => 'RPL',
            'nomor_kelas' => '1'
        ]);

        // 3. Mapel
        $mapel1 = Mapel::create(['nama_mapel' => 'Pemrograman Laravel']);
        $mapel2 = Mapel::create(['nama_mapel' => 'Basis Data']);
        $mapel3 = Mapel::create(['nama_mapel' => 'UI/UX Design']);

        // 4. Data Guru (Total 3 Guru)
        $dataGuru = [
            [
                'name' => 'Budi Guru, S.Kom',
                'email' => 'budi@test.com',
                'nip' => '198801012023011001',
                'mapel_id' => $mapel1->id,
                'jam_mulai' => '07:30:00',
                'jam_selesai' => '09:30:00'
            ],
            [
                'name' => 'Siti Aminah, S.T',
                'email' => 'siti@test.com',
                'nip' => '199202022023012002',
                'mapel_id' => $mapel2->id,
                'jam_mulai' => '09:45:00', // Lanjut setelah Budi
                'jam_selesai' => '11:45:00'
            ],
            [
                'name' => 'Eko Prasetyo, M.Kom',
                'email' => 'eko@test.com',
                'nip' => '198503032023011003',
                'mapel_id' => $mapel3->id,
                'jam_mulai' => '13:00:00', // Jam siang
                'jam_selesai' => '15:00:00'
            ],
        ];

        $guru_users = [];
        foreach ($dataGuru as $data) {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'role' => 'guru',
            ]);

            Guru::create([
                'user_id' => $user->id,
                'nama_guru' => $data['name'],
                'NIP' => $data['nip']
            ]);

            $guru_users[] = [
                'user_id' => $user->id,
                'mapel_id' => $data['mapel_id'],
                'jam_mulai' => $data['jam_mulai'],
                'jam_selesai' => $data['jam_selesai']
            ];
        }

        // 5. Siswa
        $uSiswa = User::create([
            'name' => 'Ahmad Siswa',
            'email' => 'siswa@test.com',
            'password' => Hash::make('password'),
            'role' => 'siswa',
        ]);

        $siswa = Siswa::create([
            'user_id' => $uSiswa->id,
            'id_kelas' => $kelas->id,
            'nama_siswa' => 'Ahmad Siswa',
            'NIS' => '222310101'
        ]);

        // 6. Jadwal (Semua Guru mengajar di hari yang sama dengan jam berbeda)
        $haris = ['Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'];
        
        foreach ($haris as $hari) {
            foreach ($guru_users as $g) {
                $j = Jadwal::create([
                    'kelas_id' => $kelas->id,
                    'mapel_id' => $g['mapel_id'],
                    'guru_id' => $g['user_id'], 
                    'hari' => $hari,
                    'jam_mulai' => $g['jam_mulai'],
                    'jam_selesai' => $g['jam_selesai'],
                ]);

                // 7. Sesi QR & Absensi Otomatis (Untuk demo 9 Feb - 16 Feb 2026)
                $start = Carbon::create(2026, 2, 9);
                for ($i = 0; $i <= 7; $i++) {
                    $tgl = $start->copy()->addDays($i);
                    $hariIndo = $this->getHariIndo($tgl->format('l'));

                    if ($hariIndo == $hari) {
                        $sesi = Sesi::create([
                            'jadwal_id' => $j->id,
                            'tanggal' => $tgl->format('Y-m-d'),
                            'token_qr' => Str::random(32),
                        ]);

                        // Isi absensi jika tanggal sudah lewat/hari ini
                        if ($tgl->isPast() || $tgl->isToday()) {
                            \App\Models\Absensi::create([
                                'siswa_id' => $siswa->id,
                                'sesi_id' => $sesi->id,
                                'waktu_scan' => $tgl->copy()->setTimeFromTimeString($g['jam_mulai'])->addMinutes(rand(5, 15)),
                                'status' => 'Hadir'
                            ]);
                        }
                    }
                }
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