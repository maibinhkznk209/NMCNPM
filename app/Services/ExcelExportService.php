<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use Carbon\Carbon;

class ExcelExportService
{
    /**
     * Export genre statistics to Excel
     */
    public function exportGenreStatistics($reportData, $month, $year)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'Báo Cáo Thống Kê Tình Hình Mượn Sách Theo Thể Loại');
        $sheet->mergeCells('A1:E1');
        $sheet->setCellValue('A2', 'Tháng: ' . $month . '/' . $year);
        $sheet->mergeCells('A2:E2');

        // Set headers
        $headers = ['STT', 'Tên Thể Loại', 'Số Lượt Mượn', 'Tỉ Lệ (%)', 'Danh Sách Sách'];
        $sheet->fromArray($headers, null, 'A4');

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4472C4'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:E4')->applyFromArray($headerStyle);

        // Add data
        $row = 5;
        foreach ($reportData['genres'] as $index => $genre) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $genre['name']);
            $sheet->setCellValue('C' . $row, $genre['borrow_count']);
            $sheet->setCellValue('D' . $row, $genre['percentage']);
            $sheet->setCellValue('E' . $row, implode('; ', $genre['books']));
            $row++;
        }

        // Add summary
        $summaryRow = $row + 1;
        $sheet->setCellValue('A' . $summaryRow, 'Tổng số lượt mượn: ' . $reportData['total_borrows']);
        $sheet->mergeCells('A' . $summaryRow . ':E' . $summaryRow);

        // Style data
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A4:E' . ($row - 1))->applyFromArray($dataStyle);

        // Auto size columns
        foreach (range('A', 'E') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Style title
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:A2')->applyFromArray($titleStyle);

        // Create filename
        $filename = 'bao-cao-the-loai-' . $month . '-' . $year . '.xlsx';

        return $this->downloadExcel($spreadsheet, $filename);
    }

    /**
     * Export overdue books to Excel
     */
    public function exportOverdueBooks($reportData, $date)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Set title
        $sheet->setCellValue('A1', 'Báo Cáo Thống Kê Sách Trả Trễ');
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A2', 'Ngày: ' . Carbon::createFromFormat('Y-m-d', $date)->format('d/m/Y'));
        $sheet->mergeCells('A2:G2');

        // Set headers
        $headers = ['STT', 'Tên Sách', 'Độc Giả', 'Ngày Mượn', 'Số Ngày Trễ', 'Trạng Thái', 'Tiền Phạt'];
        $sheet->fromArray($headers, null, 'A4');

        // Style headers
        $headerStyle = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
            ],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => 'C5504B'],
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ];
        $sheet->getStyle('A4:G4')->applyFromArray($headerStyle);

        // Add data
        $row = 5;
        foreach ($reportData['overdue_books'] as $index => $book) {
            $sheet->setCellValue('A' . $row, $index + 1);
            $sheet->setCellValue('B' . $row, $book['book_title']);
            $sheet->setCellValue('C' . $row, $book['reader_name']);
            $sheet->setCellValue('D' . $row, Carbon::parse($book['borrow_date'])->format('d/m/Y'));
            $sheet->setCellValue('E' . $row, $book['overdue_days']);
            $sheet->setCellValue('F' . $row, $book['status']);
            $sheet->setCellValue('G' . $row, number_format($book['fine_amount']));
            $row++;
        }

        // Add summary
        $summaryRow = $row + 1;
        $sheet->setCellValue('A' . $summaryRow, 'Tổng số sách trả trễ: ' . $reportData['total_overdue']);
        $sheet->mergeCells('A' . $summaryRow . ':G' . $summaryRow);
        
        $summaryRow2 = $summaryRow + 1;
        $sheet->setCellValue('A' . $summaryRow2, 'Tổng tiền phạt: ' . number_format($reportData['total_fine']) . 'đ');
        $sheet->mergeCells('A' . $summaryRow2 . ':G' . $summaryRow2);

        // Style data
        $dataStyle = [
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
            'alignment' => [
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ];
        $sheet->getStyle('A4:G' . ($row - 1))->applyFromArray($dataStyle);

        // Auto size columns
        foreach (range('A', 'G') as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        // Style title
        $titleStyle = [
            'font' => [
                'bold' => true,
                'size' => 14,
            ],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
            ],
        ];
        $sheet->getStyle('A1:A2')->applyFromArray($titleStyle);

        // Create filename
        $filename = 'bao-cao-tra-tre-' . $date . '.xlsx';

        return $this->downloadExcel($spreadsheet, $filename);
    }

    /**
     * Download Excel file
     */
    private function downloadExcel($spreadsheet, $filename)
    {
        $writer = new Xlsx($spreadsheet);
        
        // Create temporary file
        $tempFile = tempnam(sys_get_temp_dir(), 'excel_');
        $writer->save($tempFile);

        // Return response
        return response()->download($tempFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }
} 