<?php

namespace App\Services;

use App\Models\PointLedger;
use App\Models\PointRule;
use App\Models\UserToken;
use App\Models\Siswa;
use Illuminate\Support\Facades\DB;

class PointService
{
    /**
     * Ambil saldo terakhir dari Ledger (Buku Besar)
     */
    public function getSiswaBalance($siswaId)
    {
        // Mengambil langsung dari kolom points_store di tabel siswa
        return \App\Models\Siswa::where('id', $siswaId)->value('points_store') ?? 0;
    }
    // public function getSiswaBalance($siswaId)
    // {
    //     return PointLedger::where('siswa_id', $siswaId)
    //         ->latest()
    //         ->value('current_balance') ?? 0;
    // }

    /**
     * Logika Ledger: Mencatat mutasi poin dan update saldo di tabel Siswa
     */
    public function addTransaction($siswaId, $type, $amount, $description)
    {
        return DB::transaction(function () use ($siswaId, $type, $amount, $description) {
            $lastBalance = $this->getSiswaBalance($siswaId);
            $newBalance = $lastBalance + $amount;

            // 1. Simpan ke Ledger (Audit Trail sesuai soal)
            $ledger = PointLedger::create([
                'siswa_id' => $siswaId,
                'transaction_type' => $type,
                'amount' => $amount,
                'current_balance' => $newBalance,
                'description' => $description
            ]);

            // 2. Update kolom points_store di tabel siswa (Sesuai migration Siswa Anda)
            Siswa::where('id', $siswaId)->update(['points_store' => $newBalance]);

            return $ledger;
        });
    }

    /**
     * Rule Engine Baru: Evaluasi berdasarkan STATUS (Alpa/Izin/Sakit)
     */
    public function evaluateStatusPoin($siswaId, $status, $role)
    {
        // Mencari aturan dimana operatornya '=' dan nilainya cocok dengan status (misal: alpa)
        $rules = PointRule::where('target_role', $role)
            ->where('condition_operator', '=')
            ->where('condition_value', $status)
            ->get();
        
        foreach ($rules as $rule) {
            $type = $rule->point_modifier > 0 ? 'EARN' : 'PENALTY';
            $this->addTransaction(
                $siswaId, 
                $type, 
                $rule->point_modifier, 
                "Poin otomatis: " . $rule->rule_name . " (Status: $status)"
            );
        }
    }

    /**
     * Rule Engine Lama: Evaluasi berdasarkan WAKTU (Tepat waktu/Telat)
     */
    public function evaluateAttendancePoin($siswaId, $waktuScan, $role)
    {
        // Hanya ambil aturan yang berhubungan dengan waktu (operator < atau >)
        $rules = PointRule::where('target_role', $role)
            ->whereIn('condition_operator', ['<', '>'])
            ->get();
        
        foreach ($rules as $rule) {
            $match = false;
            $condition = $rule->condition_value;

            if ($rule->condition_operator == '<' && $waktuScan < $condition) $match = true;
            if ($rule->condition_operator == '>' && $waktuScan > $condition) $match = true;

            if ($match) {
                $type = $rule->point_modifier > 0 ? 'EARN' : 'PENALTY';
                $this->addTransaction($siswaId, $type, $rule->point_modifier, "Poin otomatis: " . $rule->rule_name);
            }
        }
    }

    /**
     * Interceptor: Otomatisasi penggunaan token jika terlambat
     */
    public function useTokenIfLate($siswaId)
    {
        // Cari token AVAILABLE milik siswa
        $token = UserToken::where('siswa_id', $siswaId)
            ->where('status', 'AVAILABLE')
            ->first();

        if ($token) {
            $token->update([
                'status' => 'USED',
                'used_at' => now()
            ]);
            return $token;
        }

        return null;
    }
}