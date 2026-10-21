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
     * Download Excel (.xls) file with formatted structure.
     */
    public static function downloadXls(string $filename, string $title, array $headers, array $rows, ?array $summaryRow = null)
    {
        if (!str_ends_with(strtolower($filename), '.xls')) {
            $filename .= '.xls';
        }

        $html = '<html xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns="http://www.w3.org/TR/REC-html40">';
        $html .= '<head><meta http-equiv="Content-Type" content="text/html; charset=UTF-8">';
        $html .= '<!--[if gte mso 9]><xml><x:ExcelWorkbook><x:ExcelWorksheets><x:ExcelWorksheet>';
        $html .= '<x:Name>Laporan</x:Name><x:WorksheetOptions><x:DisplayGridlines/></x:WorksheetOptions>';
        $html .= '</x:ExcelWorksheet></x:ExcelWorksheets></x:ExcelWorkbook></xml><![endif]-->';
        $html .= '<style>';
        $html .= 'body { font-family: Calibri, Arial, sans-serif; font-size: 11pt; }';
        $html .= 'table { border-collapse: collapse; width: 100%; }';
        $html .= 'th { background-color: #1B5E20; color: #FFFFFF; font-weight: bold; border: 1px solid #000000; padding: 6px 10px; text-align: left; }';
        $html .= 'td { border: 1px solid #D0D0D0; padding: 5px 8px; vertical-align: top; }';
        $html .= '.title-header { font-size: 14pt; font-weight: bold; color: #1B5E20; margin-bottom: 5px; }';
        $html .= '.meta-text { font-size: 9pt; color: #666666; margin-bottom: 15px; }';
        $html .= '.summary-row td { background-color: #E8F5E9; font-weight: bold; border-top: 2px solid #1B5E20; }';
        $html .= '.num { mso-number-format: "\#\,\#\#0"; text-align: right; }';
        $html .= '</style>';
        $html .= '</head><body>';

        $html .= '<div class="title-header">' . htmlspecialchars($title) . ' - PANEN MANDIRI</div>';
        $html .= '<div class="meta-text">Diunduh pada: ' . date('d/m/Y H:i') . ' WIB</div>';

        $html .= '<table border="1">';
        $html .= '<thead><tr>';
        foreach ($headers as $header) {
            $html .= '<th>' . htmlspecialchars($header) . '</th>';
        }
        $html .= '</tr></thead>';

        $html .= '<tbody>';
        foreach ($rows as $row) {
            $html .= '<tr>';
            foreach ($row as $cell) {
                $cleanCell = is_string($cell) ? trim(strip_tags($cell)) : $cell;
                $isNumeric = is_numeric($cleanCell);
                $class = $isNumeric ? ' class="num"' : '';
                $html .= '<td' . $class . '>' . htmlspecialchars((string) $cleanCell) . '</td>';
            }
            $html .= '</tr>';
        }
        $html .= '</tbody>';

        if (!empty($summaryRow)) {
            $html .= '<tfoot><tr class="summary-row">';
            foreach ($summaryRow as $cell) {
                $cleanCell = is_string($cell) ? trim(strip_tags($cell)) : $cell;
                $html .= '<td>' . htmlspecialchars((string) $cleanCell) . '</td>';
            }
            $html .= '</tr></tfoot>';
        }

        $html .= '</table>';
        $html .= '</body></html>';

        return response($html, 200, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
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
