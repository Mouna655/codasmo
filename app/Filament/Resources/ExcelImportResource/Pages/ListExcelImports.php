<?php

namespace App\Filament\Resources\ExcelImportResource\Pages;

use App\Filament\Resources\ExcelImportResource;
use App\Imports\{DailyProductionImport, CsvDailyProductionImport};
use App\Models\ExcelImportLog;
use App\Events\DashboardUpdated;
use App\Http\Controllers\DashboardController;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Storage;
use App\Models\DailyProduction;


class ListExcelImports extends ListRecords
{
    protected static string $resource = ExcelImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_excel')
                ->label('Upload File Excel / CSV')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Upload Data Produksi Harian')
                ->modalDescription('Upload file Excel (.xlsx) atau CSV (.csv) dengan format Daily Production Report.')
                ->modalWidth('lg')
                ->form([
                    Forms\Components\DatePicker::make('report_date')
                        ->label('Tanggal Laporan')
                        ->required()
                        ->default(today())
                        ->maxDate(today())
                        ->displayFormat('d M Y')
                        ->native(false)
                        ->helperText('Tanggal data produksi dalam file.'),

                    Forms\Components\FileUpload::make('data_file')
                        ->label('File (.xlsx atau .csv)')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                            'text/csv',
                            'application/csv',
                        ])
                        ->maxSize(10240) // 10 MB
                        ->disk('local')
                        ->directory('excel-imports')
                        ->helperText('Format: Draft_Daily.xlsx atau data.csv (delimiter: semicolon)')
                        ->moveFiles(),
                ])
                ->action(function (array $data): void {
                    $this->processUpload($data);
                }),
        ];
    }

    /**
     * Handle file upload - detect format dan route ke appropriate importer
     */
    private function processUpload(array $data): void
    {
        try {
            $reportDate = Carbon::parse($data['report_date'])->format('Y-m-d');
            //$filePath = storage_path('app/' . $data['data_file']);
            $relativePath = $data['data_file'];

            if (!Storage::disk('local')->exists($relativePath)) {
                throw new \Exception("File tidak ditemukan di storage: " . $relativePath);
            }

            $filePath = Storage::disk('local')->path($relativePath);
            $filename = basename($data['data_file']);
            $fileExt = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

            // Create import log record
            $log = ExcelImportLog::create([
                'report_date' => $reportDate,
                'filename' => $filename,
                'file_type' => $fileExt === 'csv' ? 'csv' : 'xlsx',
                'rows_imported' => 0,
                'errors_count' => 0,
                'status' => 'pending',
                'uploaded_by' => auth()->id(),
            ]);

            // Route ke appropriate importer based on file type
            if ($fileExt === 'csv') {
                $this->importCsv($log, $filePath, $reportDate);
            } else {
                $this->importExcel($log, $filePath, $reportDate);
            }

        } catch (\Exception $e) {
            Notification::make()
                ->title('Error Upload')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Process Excel file upload
     */
    private function importExcel(ExcelImportLog $log, string $filePath, string $reportDate): void
    {
        try {
            // Create importer dengan link ke log
            $importer = new DailyProductionImport($reportDate);
            $importer->setImportLog($log);

            if (!file_exists($filePath)) {
                throw new \Exception("File tidak ditemukan: " . $filePath);
            }
           
            // 🔥 ambil semua site dari file (optional advanced)
            DailyProduction::where('report_date', $reportDate)
                ->update(['is_active' => false]);

             // Import
            Excel::import($importer, $filePath);
            
            // Finalize
            $importer->finalize();
            
            // Get error count dari logger
            $errorCount = $importer->getErrorLogger()->getErrorCount();
            $rowsImported = $importer->imported;

            // Determine status
            if ($errorCount === 0) {
                $status = $rowsImported > 0 ? 'success' : 'failed';
            } else {
                $status = $rowsImported > 0 ? 'partial' : 'failed';
            }

            // Update log
            $log->update([
                'rows_imported' => $rowsImported,
                'errors_count' => $errorCount,
                'status' => $status,
                'errors' => !empty($importer->errors) ? implode("\n", $importer->errors) : null,
            ]);

            \Log::info('Import file path: ' . $filePath);

            // Broadcast realtime update
            if ($rowsImported > 0) {
                $controller = new DashboardController();
                $payload = $controller->build(Carbon::parse($reportDate));
                broadcast(new DashboardUpdated($reportDate, $payload))->toOthers();
            }

            // Notifikasi
            $this->notifyImportResult($rowsImported, $errorCount, $status);

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'errors' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Import Excel Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Process CSV file upload
     */
    private function importCsv(ExcelImportLog $log, string $filePath, string $reportDate): void
    {
        try {
            // Create importer
            $importer = new CsvDailyProductionImport($reportDate, $log->id);

            // Import
            $result = $importer->import($filePath);
            
            if (!$result) {
                // Parse error atau format error
                $log->update([
                    'status' => 'failed',
                    'errors' => 'Format CSV tidak sesuai. Cek file headers dan delimiter (;)',
                ]);
                
                Notification::make()
                    ->title('Import CSV Gagal - Format Error')
                    ->body('Format file tidak sesuai. Pastikan: delimiter (;), headers benar, tidak ada spasi di awal baris.')
                    ->danger()
                    ->send();
                return;
            }

            // Get metrics
            $errorCount = $importer->getErrorLogger()->getErrorCount();
            $rowsImported = $importer->getImported();

            // Determine status
            if ($errorCount === 0) {
                $status = $rowsImported > 0 ? 'success' : 'failed';
            } else {
                $status = $rowsImported > 0 ? 'partial' : 'failed';
            }

            // Update log
            $log->update([
                'rows_imported' => $rowsImported,
                'errors_count' => $errorCount,
                'status' => $status,
                'errors' => !empty($importer->getErrors()) ? implode("\n", $importer->getErrors()) : null,
            ]);

            // Broadcast realtime update
            if ($rowsImported > 0) {
                $controller = new DashboardController();
                $payload = $controller->build(Carbon::parse($reportDate));
                broadcast(new DashboardUpdated($reportDate, $payload))->toOthers();
            }

            // Notifikasi
            $this->notifyImportResult($rowsImported, $errorCount, $status);

        } catch (\Exception $e) {
            $log->update([
                'status' => 'failed',
                'errors' => $e->getMessage(),
            ]);

            Notification::make()
                ->title('Import CSV Gagal')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    /**
     * Show notification based on import result
     */
    private function notifyImportResult(int $imported, int $errors, string $status): void
    {
        $title = match($status) {
            'success' => "✅ Berhasil - $imported baris tersimpan",
            'partial' => "⚠️ Sebagian Besar - $imported baris tersimpan, $errors error",
            'failed' => "❌ Gagal",
            default => "Import Selesai"
        };

        $body = match($status) {
            'success' => 'Semua data berhasil diimport dan dashboard sudah terupdate.',
            'partial' => 'Lihat detail error agar dapat diperbaiki di upload berikutnya.',
            'failed' => 'Tidak ada data yang berhasil diimport. Cek format file.',
            default => 'Import process selesai.'
        };

        $color = match($status) {
            'success' => 'success',
            'partial' => 'warning',
            'failed' => 'danger',
            default => 'info'
        };

        Notification::make()
            ->title($title)
            ->body($body)
            ->$color()
            ->send();
    }
}