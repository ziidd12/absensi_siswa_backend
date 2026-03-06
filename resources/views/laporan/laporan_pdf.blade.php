<!-- resources/views/laporan/laporan_pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; color: #333; }
        .header { text-align: center; border-bottom: 2px solid #444; padding-bottom: 10px; margin-bottom: 20px; }
        .info { margin-bottom: 15px; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #999; padding: 6px; text-align: left; }
        th { background-color: #f0f0f0; }
        
        /* Warna Status */
        .status-hadir { color: #2ecc71; font-weight: bold; }
        .status-sakit { color: #f1c40f; font-weight: bold; }
        .status-izin { color: #3498db; font-weight: bold; }
        .status-alpa { color: #e74c3c; font-weight: bold; }
        
        .summary-box { margin-top: 20px; width: 300px; }
        .summary-box td { border: none; padding: 3px; }
    </style>
</head>
<body>
    <div class="header">
        <h2 style="margin:0;">LAPORAN KEHADIRAN SISWA</h2>
        <p style="margin:5px 0;">Periode Tahun Ajaran ID: {{ $filter['tahun_ajaran_id'] ?? '-' }}</p>
        <p style="margin:0;">Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <div class="info">
        <strong>Filter Terpilih:</strong><br>
        Tingkat: {{ $filter['tingkat'] ?? 'Semua' }} | 
        Jurusan: {{ $filter['jurusan'] ?? 'Semua' }} | 
        Status: {{ $filter['status'] ?? 'Semua' }}
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama Siswa</th>
                <th>Kelas</th>
                <th>Mapel</th>
                <th>Waktu</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absensi as $key => $row)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $row->siswa->nama_siswa ?? 'Tanpa Nama' }}</td>
                <td>
                    {{ $row->siswa->kelas->tingkat ?? '' }} 
                    {{ $row->siswa->kelas->jurusan ?? '' }} 
                    {{ $row->siswa->kelas->nomor_kelas ?? '' }}
                </td>
                <td>{{ $row->sesi->jadwal->mapel->nama_mapel ?? '-' }}</td>
                <td>{{ $row->waktu_scan ?? '-' }}</td>
                <td>{{ $row->status }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <strong>Ringkasan Statistik:</strong>
        <table>
            <tr><td>Total Hadir</td><td>: {{ $stats['Hadir'] }}</td></tr>
            <tr><td>Total Sakit</td><td>: {{ $stats['Sakit'] }}</td></tr>
            <tr><td>Total Izin</td><td>: {{ $stats['Izin'] }}</td></tr>
            <tr><td>Total Alpa</td><td>: {{ $stats['Alpa'] }}</td></tr>
            <tr style="font-weight:bold; border-top:1px solid #333;">
                <td>TOTAL DATA</td><td>: {{ $total }}</td>
            </tr>
        </table>
    </div>
</body>
</html>