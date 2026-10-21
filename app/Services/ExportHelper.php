<?php

namespace App\Services;

use Symfony\Component\HttpFoundation\StreamedResponse;

class ExportHelper
{
    /**
     * Download CSV formatted for Microsoft Excel with UTF-8 BOM.
     */
    public static function downloadCsv(string $filename, array $headers, array $rows): StreamedResponse
    {
        $callback = function () use ($headers, $rows) {
            $handle = fopen('php://output', 'w');
            
            // Write UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Write header columns
            fputcsv($handle, $headers);

            // Write data rows
            foreach ($rows as $row) {
                // Strip HTML tags if any
                $cleanRow = array_map(function ($val) {
                    if (is_string($val)) {
                        return trim(strip_tags($val));
                    }
                    return $val;
                }, $row);

                fputcsv($handle, $cleanRow);
            }

            fclose($handle);
        };

        return response()->streamDownload($callback, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Cache-Control' => 'no-cache, no-store, must-revalidate',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }

    /**
     * Render the official printable report layout (with instant print/PDF capability).
     */
    public static function renderPrintView(
        string $title,
        array $columns,
        array $rows,
        ?string $filterText = null,
        ?array $summaryRow = null,
        string $orientation = 'portrait'
    ) {
        return view('exports.print', [
            'title' => $title,
            'columns' => $columns,
            'rows' => $rows,
            'filterText' => $filterText,
            'summaryRow' => $summaryRow,
            'orientation' => $orientation,
        ]);
    }
}
