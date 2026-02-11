<!DOCTYPE html>
<html>
<head>
    <title>Laporan Absensi</title>
    <style>
        body { font-family: sans-serif; font-size: 11px; }
        .header { text-align: center; border-bottom: 2px solid #333; padding-bottom: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f4f4f4; }
        .status-hadir { color: green; font-weight: bold; }
        .status-alpa { color: red; font-weight: bold; }
        .bar-container { background: #eee; width: 200px; height: 10px; display: inline-block; }
        .bar-fill { height: 10px; background: blue; }
    </style>
</head>
<body>
    <div class="header">
        <h2>LAPORAN KEHADIRAN SISWA</h2>
        <p>Dicetak pada: {{ date('d/m/Y H:i') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th>Nama Siswa</th>
                <th>Mata Pelajaran</th>
                <th>Waktu Scan</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($absensi as $key => $row)
            <tr>
                <td>{{ $key + 1 }}</td>
                <td>{{ $row->siswa->nama_siswa ?? '-' }}</td>
                <td>{{ $row->sesi->jadwal->mapel->nama_mapel ?? '-' }}</td>
                <td>{{ \Carbon\Carbon::parse($row->waktu_scan)->format('d/m/Y H:i') }}</td>
                <td>
                    <span class="{{ strtolower($row->status) == 'hadir' ? 'status-hadir' : 'status-alpa' }}">
                        {{ strtoupper($row->status) }}
                    </span>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>