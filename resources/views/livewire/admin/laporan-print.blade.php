<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Laporan Bantuan Gizi</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #333; }
        h1 { font-size: 16px; margin-bottom: 4px; }
        p.subtitle { color: #666; margin-bottom: 16px; font-size: 11px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 6px 8px; text-align: left; }
        th { background: #f5f5f5; font-weight: 600; }
        tfoot td { font-weight: 600; background: #f5f5f5; }
        .print-btn { margin-bottom: 12px; }
        @media print { .print-btn { display: none; } }
    </style>
</head>
<body>
    <div class="print-btn">
        <button onclick="window.print()">Cetak</button>
        <button onclick="window.close()">Tutup</button>
    </div>

    <h1>Laporan Penyaluran Bantuan Gizi Lansia</h1>
    <p class="subtitle">Dicetak pada: {{ now()->translatedFormat('d F Y H:i') }}</p>

    <table>
        <thead>
            <tr>
                <th>No.</th>
                <th>NIK</th>
                <th>Nama</th>
                <th>Usia</th>
                <th>RW</th>
                <th>Periode</th>
                <th>Status</th>
                <th>Skor Ranking</th>
            </tr>
        </thead>
        <tbody>
            @foreach($data as $item)
            <tr>
                <td>{{ $loop->iteration }}</td>
                <td>{{ $item->lansia?->nik }}</td>
                <td>{{ $item->lansia?->nama }}</td>
                <td>{{ $item->lansia?->usia }}</td>
                <td>{{ $item->lansia?->rw }}</td>
                <td>{{ $item->periode_bulan }}/{{ $item->periode_tahun }}</td>
                <td>{{ $item->status_penerima }}</td>
                <td>{{ $item->skor_ranking ? number_format($item->skor_ranking, 4) : '-' }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="6">Total</td>
                <td colspan="2">{{ $data->count() }} data</td>
            </tr>
        </tfoot>
    </table>
</body>
</html>
