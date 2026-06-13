<?php

namespace App\Support;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * 報表匯出 — 對齊 yems lib/reports/export.js 的行為：
 * - CSV：UTF-8 BOM + CRLF（Excel 開中文不亂碼）
 * - XLSX：第一列粗體 + freeze + autoFilter（用 PhpSpreadsheet，對應 yems 的 exceljs）
 */
class ReportExport
{
    /** @param string[] $headers  @param array<int,array<int,string|int|null>> $rows */
    public static function csv(array $headers, array $rows, string $filename): StreamedResponse
    {
        return response()->streamDownload(function () use ($headers, $rows) {
            echo "\xEF\xBB\xBF"; // UTF-8 BOM
            $out = fopen('php://output', 'w');
            self::fputCrlf($out, $headers);
            foreach ($rows as $row) {
                self::fputCrlf($out, $row);
            }
            fclose($out);
        }, $filename, ['Content-Type' => 'text/csv; charset=utf-8']);
    }

    public static function xlsx(array $headers, array $rows, string $filename): Response
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->fromArray($headers, null, 'A1');
        $sheet->fromArray($rows, null, 'A2');

        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}1")->getFont()->setBold(true);
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastCol}1");
        foreach (range(1, count($headers)) as $col) {
            $sheet->getColumnDimensionByColumn($col)->setAutoSize(true);
        }

        $writer = new Xlsx($spreadsheet);
        ob_start();
        $writer->save('php://output');
        $content = ob_get_clean();

        return response($content, 200, [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ]);
    }

    private static function fputCrlf($handle, array $fields): void
    {
        $csv = [];
        foreach ($fields as $f) {
            $s = (string) ($f ?? '');
            if (preg_match('/[",\r\n]/', $s)) {
                $s = '"' . str_replace('"', '""', $s) . '"';
            }
            $csv[] = $s;
        }
        fwrite($handle, implode(',', $csv) . "\r\n");
    }
}
