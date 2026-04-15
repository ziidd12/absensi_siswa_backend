<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Siswa;
use App\Models\Absensi;
use App\Models\PoinHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AutoAlphaPoint extends Command
{
    // Ngaran paréntahna
    protected $signature = 'auto:alpha';

    // Katerangan paréntah
    protected $description = 'Méré denda -20 poin otomatis ka siswa nu teu absen poé ieu (Saptu-Minggu libur)';

    public function handle()
    {
        $hariIeu = Carbon::now();
        $tglFormat = $hariIeu->toDateString();

        // 1. CEK POÉ LIBUR (Saptu & Minggu moal aya denda)
        if ($hariIeu->isWeekend()) {
            $this->info("Dinten libur mah moal aya denda, Lekk! Enjoy weekendna.");
            return;
        }

        $semuaSiswa = Siswa::all();
        $count = 0;

        foreach ($semuaSiswa as $siswa) {
            // 2. CEK Naha aya data absen poé ieu?
            // (Asumsi: Mun aya data 'Hadir', 'Sakit', atawa 'Izin' di tabel Absensi, hartina teu Alfa)
            $absen = Absensi::where('siswa_id', $siswa->id)
                        ->whereDate('created_at', $tglFormat)
                        ->exists();

            // 3. CEK Naha poé ieu geus pernah meunang denda Alfa? (Meh teu double denda)
            $sudahDenda = PoinHistory::where('siswa_id', $siswa->id)
                            ->whereDate('created_at', $tglFormat)
                            ->where('keterangan', 'LIKE', '%Alfa%')
                            ->exists();

            // KONDISI: Mun euweuh absen pisan JEUNG can meunang denda poé ieu
            if (!$absen && !$sudahDenda) {
                DB::transaction(function () use ($siswa) {
                    // Kurangan poin di tabel siswa
                    $siswa->points_store -= 20;
                    $siswa->save();

                    // Jieun log riwayat poin
                    PoinHistory::create([
                        'siswa_id'       => $siswa->id,
                        'poin_perubahan' => -20,
                        'keterangan'     => 'Alfa (Tidak ada keterangan absen)',
                    ]);
                });
                $count++;
            }
        }

        if ($count > 0) {
            $this->info("Suksés! Aya $count siswa nu meunang denda Alfa poé ieu.");
        } else {
            $this->info("Alhamdulillah, sadayana absen atanapi parantos kenging denda.");
        }
    }
}