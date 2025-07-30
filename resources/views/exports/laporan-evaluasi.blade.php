<!DOCTYPE html>
<html>
<head>
    <title>Laporan Evaluasi Kinerja - {{ $laporan->kode_laporan }}</title>
    <style>
        body { font-family: Arial, sans-serif; font-size: 12px; }
        .header { text-align: center; margin-bottom: 20px; }
        .info-table { width: 100%; margin-bottom: 20px; border-collapse: collapse; }
        .info-table td { padding: 5px; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th, .data-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        .data-table th { background-color: #f2f2f2; font-weight: bold; }
        .summary { margin-top: 20px; padding: 10px; background-color: #f9f9f9; }
        .flex { display: flex; justify-content: space-between; align-items: center; }
        .flex img { max-height: 50px; }
        .items-center { align-items: center; }
        .justify-between { justify-content: space-between; }
    </style>
</head>
<body>
    <div class="header">
        <div class="flex items-center justify-between">
            <div style="width: 100px; height: 50px; display: flex; align-items: center;">
                <img src="https://r2.wakafsalman.or.id/logo_baru_ws.jpg" alt="Logo" style="height: 50px;">
            </div>
            <div>
                <h2>Laporan Evaluasi Kinerja</h2>
                <p>{{ $laporan->divisi->nama }}</p>
            </div>
            <div></div>
        </div>
    </div>

    <table class="info-table">
        <tr>
            <td width="20%"><strong>Kode Laporan:</strong></td>
            <td>{{ $laporan->kode_laporan }}</td>
            <td width="20%"><strong>Periode:</strong></td>
            <td>{{ $laporan->periode_mulai->format('d/m/Y') }} - {{ $laporan->periode_selesai->format('d/m/Y') }}</td>
        </tr>
        <tr>
            <td><strong>Divisi:</strong></td>
            <td>{{ $laporan->divisi->nama }}</td>
            <td><strong>Tanggal Laporan:</strong></td>
            <td>{{ $laporan->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        @if($laporan->tipe_laporan === 'individual')
        <tr>
            <td><strong>Karyawan:</strong></td>
            <td>{{ $laporan->user->name }}</td>
            <td><strong>Jabatan:</strong></td>
            <td>{{ $laporan->user->jabatan ?? '-' }}</td>
        </tr>
        @endif
    </table>

    <h4>REALISASI KPI</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode KPI</th>
                <th>Activity</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Score</th>
                <th>Periode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($kpiData as $index => $kpi)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $kpi['kode'] }}</td>
                <td>{{ $kpi['activity'] }}</td>
                <td>-</td>
                <td>{{ $kpi['nilai'] }}</td>
                <td>{{ $kpi['nilai'] }}/100</td>
                <td>{{ $kpi['periode'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; font-style: italic;">Tidak ada data KPI</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <h4>REALISASI OKR</h4>
    <table class="data-table">
        <thead>
            <tr>
                <th>No</th>
                <th>Kode OKR</th>
                <th>Activity</th>
                <th>Target</th>
                <th>Realisasi</th>
                <th>Score</th>
                <th>Periode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($okrData as $index => $okr)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $okr['kode'] }}</td>
                <td>{{ $okr['activity'] }}</td>
                <td>-</td>
                <td>{{ $okr['nilai'] }}</td>
                <td>{{ $okr['nilai'] }}/100</td>
                <td>{{ $okr['periode'] }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="7" style="text-align: center; font-style: italic;">Tidak ada data OKR</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="summary">
        <h4>SUMMARY EVALUASI</h4>
        <table class="info-table">
            <tr>
                <td width="25%"><strong>Total KPI:</strong></td>
                <td width="25%">{{ $laporan->total_kpi ?? 0 }}</td>
                <td width="25%"><strong>Total OKR:</strong></td>
                <td width="25%">{{ $laporan->total_okr ?? 0 }}</td>
            </tr>
            <tr>
                <td><strong>Overall Score:</strong></td>
                <td>{{ $laporan->rata_rata_score ?? 0 }}/100</td>
                <td><strong>Status Kinerja:</strong></td>
                <td>{{ $laporan->status_kinerja ?? '-' }}</td>
            </tr>
        </table>
        
        @if($laporan->rekomendasi)
        <p><strong>Rekomendasi:</strong></p>
        <p>{{ $laporan->rekomendasi }}</p>
        @endif
        
        @if($laporan->catatan)
        <p><strong>Catatan:</strong></p>
        <p>{{ $laporan->catatan }}</p>
        @endif
    </div>
</body>
</html>
