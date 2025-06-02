<?php
if (!defined('ABSPATH')) exit;
require_once AERP_HRM_PATH . 'vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AERP_Excel_Export_Helper
{
    public static function export($headers, $data, $filename = 'export', $sheet_title = 'Sheet1')
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheet_title);

        // Header + style
        $colIndex = 1;
        foreach ($headers as $key => $title) {
            $col = Coordinate::stringFromColumnIndex($colIndex++);
            $sheet->setCellValue($col . '1', $title);
            $sheet->getColumnDimension($col)->setAutoSize(true);

            // Style header
            $sheet->getStyle($col . '1')->applyFromArray([
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => '37B34A'],
                ],
                'font' => [
                    'bold' => true,
                    'color' => ['rgb' => 'FFFFFF'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical'   => Alignment::VERTICAL_CENTER,
                ],
            ]);
        }

        // Freeze dòng đầu
        $sheet->freezePane('A2');

        // Data rows
        foreach ($data as $i => $row) {
            $colIndex = 1;
            foreach ($headers as $key => $label) {
                $col = Coordinate::stringFromColumnIndex($colIndex++);
                $cell = $col . ($i + 2);
                $value = $row[$key] ?? '';
                $sheet->setCellValue($cell, is_scalar($value) ? $value : json_encode($value));
            }
        }

        // Format tiền
        $moneyCols = ['final_salary', 'bonus', 'deduction', 'base_salary', 'advance_paid'];
        $colIndex = 1;
        foreach ($headers as $key => $label) {
            if (in_array($key, $moneyCols)) {
                $col = Coordinate::stringFromColumnIndex($colIndex);
                $sheet->getStyle("{$col}2:{$col}" . (count($data) + 1))
                    ->getNumberFormat()->setFormatCode('#,##0 [$₫-vi-VN]');
            }
            $colIndex++;
        }

        // Viền bảng
        $rowCount = count($data) + 1;
        $lastCol = Coordinate::stringFromColumnIndex(count($headers));
        $sheet->getStyle("A1:{$lastCol}{$rowCount}")
            ->getBorders()->getAllBorders()
            ->setBorderStyle(Border::BORDER_THIN);

        // Zebra stripe (dòng chẵn)
        for ($r = 2; $r <= $rowCount; $r++) {
            if ($r % 2 === 0) {
                $sheet->getStyle("A{$r}:{$lastCol}{$r}")
                    ->getFill()->setFillType(Fill::FILL_SOLID)
                    ->getStartColor()->setRGB('e4e4e4');
            }
        }

        // Dọn buffer & tạo tên file an toàn
        if (ob_get_length()) ob_end_clean();

        if (!is_string($filename)) {
            if (is_array($filename)) {
                $filename = implode('-', array_values($filename));
            } else {
                $filename = 'export';
            }
        }

        $filename = sanitize_file_name($filename) . '-' . date('Ymd-His') . '.xlsx';

        // Header xuất file
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
    public static function export_raw(array $rows, $filename = 'export', $sheet_title = 'Sheet1')
    {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle($sheet_title);

        // Style config
        $titleColor = 'D9EAD3';  // xanh nhạt
        $headerColor = 'F4CCCC'; // đỏ nhạt

        $boldFont = ['font' => ['bold' => true]];
        $centerAlign = ['alignment' => ['horizontal' => 'center']];
        $borderStyle = [
            'borders' => [
                'allBorders' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]
            ]
        ];

        // Tính số cột lớn nhất
        $maxColCount = 1;
        foreach ($rows as $r) {
            if (is_array($r)) {
                $maxColCount = max($maxColCount, count($r));
            }
        }

        $rowIndex = 1;
        foreach ($rows as $i => $row) {
            if (!is_array($row)) continue;
            if (count(array_filter($row, 'strlen')) === 0) continue; // bỏ block trống

            // SECTION TIÊU ĐỀ (1 ô)
            if (count($row) === 1) {
                $cell = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1) . $rowIndex;
                $sheet->setCellValue($cell, $row[0]);

                $startCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(1);
                $endCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($maxColCount);
                $sheet->mergeCells("{$startCol}{$rowIndex}:{$endCol}{$rowIndex}");

                $sheet->getStyle($cell)
                    ->applyFromArray(array_merge($boldFont, $centerAlign))
                    ->getFill()->setFillType('solid')->getStartColor()->setRGB($titleColor);
            } else {
                // NORMAL DATA ROW
                foreach ($row as $j => $value) {
                    $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($j + 1);
                    $cell = $col . $rowIndex;

                    $sheet->setCellValue($cell, $value);
                    $sheet->getStyle($cell)->applyFromArray($borderStyle);

                    // Nếu là header sau section
                    $isHeader = (
                        $rowIndex === 1 ||
                        (isset($rows[$i - 1]) && is_array($rows[$i - 1]) && count($rows[$i - 1]) === 1)
                    );

                    if ($isHeader) {
                        $sheet->getStyle($cell)
                            ->applyFromArray(array_merge($boldFont, $centerAlign))
                            ->getFill()->setFillType('solid')->getStartColor()->setRGB($headerColor);
                    } else {
                        $sheet->getStyle($cell)->getAlignment()->setHorizontal('left');
                    }
                }
            }

            $rowIndex++;
        }

        // Auto-size columns
        for ($i = 1; $i <= $maxColCount; $i++) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i);
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        // Output
        if (ob_get_length()) ob_end_clean();
        $filename = sanitize_file_name($filename) . '-' . date('Ymd-His') . '.xlsx';

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$filename\"");
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}
