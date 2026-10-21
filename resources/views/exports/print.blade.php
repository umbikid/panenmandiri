<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }} - Panen Mandiri</title>
    <style>
        @page {
            size: A4 {{ $orientation ?? 'portrait' }};
            margin: 12mm 15mm;
        }
        * {
            box-sizing: border-box;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
        }
        body {
            color: #1f2937;
            background: #fff;
            margin: 0;
            padding: 16px;
            font-size: 11px;
            line-height: 1.4;
        }
        .no-print-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            background: #f3f4f6;
            padding: 10px 16px;
            border-radius: 8px;
            margin-bottom: 20px;
            border: 1px solid #e5e7eb;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            padding: 6px 14px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-primary {
            background: #15803d;
            color: #fff;
        }
        .btn-primary:hover {
            background: #166534;
        }
        .btn-secondary {
            background: #e5e7eb;
            color: #374151;
            margin-left: 8px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #15803d;
            padding-bottom: 12px;
            margin-bottom: 16px;
        }
        .brand-title {
            font-size: 18px;
            font-weight: 800;
            color: #14532d;
            margin: 0;
            letter-spacing: -0.5px;
        }
        .brand-subtitle {
            font-size: 10px;
            color: #6b7280;
            margin: 2px 0 0 0;
        }
        .report-title {
            font-size: 14px;
            font-weight: 700;
            color: #111827;
            margin: 0 0 4px 0;
            text-align: right;
        }
        .meta-info {
            font-size: 10px;
            color: #6b7280;
            text-align: right;
        }
        .filter-badges {
            margin-bottom: 12px;
            font-size: 10px;
            color: #4b5563;
        }
        .filter-badges strong {
            color: #111827;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
        }
        th {
            background-color: #f8fafc;
            color: #1e293b;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 9.5px;
            letter-spacing: 0.5px;
            border: 1px solid #cbd5e1;
            padding: 6px 8px;
            text-align: left;
        }
        td {
            border: 1px solid #e2e8f0;
            padding: 6px 8px;
            vertical-align: top;
        }
        tr:nth-child(even) td {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 4px;
            font-size: 9px;
            font-weight: 600;
        }
        .badge-success { background: #dcfce7; color: #15803d; }
        .badge-warning { background: #fef3c7; color: #92400e; }
        .badge-info { background: #dbeafe; color: #1e40af; }
        .badge-gray { background: #f3f4f6; color: #4b5563; }
        .footer {
            margin-top: 24px;
            padding-top: 10px;
            border-top: 1px dashed #d1d5db;
            display: flex;
            justify-content: space-between;
            font-size: 9px;
            color: #9ca3af;
        }
        @media print {
            .no-print-bar { display: none !important; }
            body { padding: 0; }
            th { background-color: #f1f5f9 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            tr:nth-child(even) td { background-color: #f8fafc !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
            .badge { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
    </style>
</head>
<body>
    <div class="no-print-bar">
        <div>
            <strong>Pratinjau Cetak / Ekspor PDF</strong> &bull; Silakan klik tombol cetak atau gunakan Ctrl+P / Cmd+P lalu pilih "Simpan sebagai PDF".
        </div>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                🖨️ Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-secondary">
                Tutup
            </button>
        </div>
    </div>

    <div class="header">
        <div>
            <h1 class="brand-title">PANEN MANDIRI</h1>
            <p class="brand-subtitle">Platform Distribusi Komoditas Pertanian, Beras & Ternak Terintegrasi</p>
        </div>
        <div>
            <div class="report-title">{{ $title }}</div>
            <div class="meta-info">
                Dicetak pada: {{ now()->translatedFormat('d F Y, H:i') }} WIB<br>
                Oleh Admin: {{ auth()->user()->name ?? 'Administrator' }}
            </div>
        </div>
    </div>

    @if(!empty($filterText))
    <div class="filter-badges">
        <strong>Filter Diterapkan:</strong> {!! $filterText !!}
    </div>
    @endif

    <table>
        <thead>
            <tr>
                @foreach($columns as $col)
                    <th class="{{ $col['class'] ?? '' }}">{{ $col['label'] }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @forelse($rows as $index => $row)
                <tr>
                    @foreach($columns as $col)
                        <td class="{{ $col['class'] ?? '' }}">
                            {!! $row[$col['key']] ?? '-' !!}
                        </td>
                    @endforeach
                </tr>
            @empty
                <tr>
                    <td colspan="{{ count($columns) }}" class="text-center" style="padding: 24px; color: #9ca3af;">
                        Tidak ada data yang sesuai untuk ditampilkan.
                    </td>
                </tr>
            @endforelse
        </tbody>
        @if(!empty($summaryRow))
            <tfoot>
                <tr style="background: #f1f5f9; font-weight: bold; border-top: 2px solid #cbd5e1;">
                    @foreach($columns as $col)
                        <td class="{{ $col['class'] ?? '' }}">
                            {!! $summaryRow[$col['key']] ?? '' !!}
                        </td>
                    @endforeach
                </tr>
            </tfoot>
        @endif
    </table>

    <div class="footer">
        <div>Dokumen ini digenerate secara otomatis oleh Sistem Panen Mandiri.</div>
        <div>Halaman 1 dari 1</div>
    </div>

    <script>
        // Auto trigger print dialog if requested
        @if(request('auto_print'))
            window.addEventListener('load', function() {
                setTimeout(function() {
                    window.print();
                }, 300);
            });
        @endif
    </script>
</body>
</html>
