<?php

namespace App\Filament\Resources\PoaImportResource\Pages;

use App\Filament\Resources\PoaImportResource;
use App\Imports\PoaImport;
use App\Models\PoaSnapshot;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListPoaImports extends ListRecords
{
    protected static string $resource = PoaImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_poa')
                ->label('Upload File POA')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Upload Data POA')
                ->modalDescription(
                    'Upload file Excel (format Dummy_Draft_POA.xlsx). ' .
                    'Sistem otomatis membaca sheet tahun terbesar. ' .
                    'Data lama tetap tersimpan — pilih tanggal untuk melihat histori.'
                )
                ->modalWidth('lg')
                ->form([
                    Forms\Components\DatePicker::make('upload_date')
                        ->label('Tanggal Laporan')
                        ->required()
                        ->default(today())
                        ->maxDate(today())
                        ->displayFormat('d M Y')
                        ->native(false)
                        ->helperText(
                            'Tanggal ini menentukan "titik waktu" snapshot. ' .
                            'User yang melihat report tanggal ini atau setelahnya ' .
                            'akan mendapat data dari file ini.'
                        ),

                    Forms\Components\FileUpload::make('excel_file')
                        ->label('File Excel (.xlsx)')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                        ])
                        ->maxSize(20480) // 20 MB
                        ->disk('local')
                        ->directory('poa-imports')
                        ->helperText(
                            'Format: Dummy_Draft_POA.xlsx. ' .
                            'Sheet yang dibaca: sheet dengan angka tahun terbesar (misal 2026, 2027).'
                        )
                        ->moveFiles(),
                ])
                ->action(function (array $data): void {
                    $uploadDate = \Carbon\Carbon::parse($data['upload_date'])->format('Y-m-d');
                    $filePath   = Storage::disk('local')
                                    ->path($data['excel_file']);

                    // Buat snapshot dulu (status: processing)
                    $snapshot = PoaSnapshot::create([
                        'upload_date'       => $uploadDate,
                        'data_year'         => 0,  // diisi importer
                        'filename'          => $data['excel_file'],
                        'original_filename' => basename($data['excel_file']),
                        'status'            => 'processing',
                        'uploaded_by'       => auth()->id(),
                    ]);

                    try {
                        $importer = new PoaImport();
                        $importer->import($filePath, $snapshot->id);

                        $status = count($importer->errors) === 0 ? 'success' : 'partial';

                        Notification::make()
                            ->title("Import berhasil — {$importer->imported} baris · Tahun {$importer->dataYear}")
                            ->body(
                                count($importer->errors) > 0
                                    ? 'Ada ' . count($importer->errors) . ' baris dilewati. Cek log.'
                                    : 'Dashboard POA sudah diperbarui.'
                            )
                            ->success()
                            ->send();

                    } catch (\Exception $e) {
                        $snapshot->update([
                            'status'        => 'failed',
                            'error_message' => $e->getMessage(),
                        ]);

                        Notification::make()
                            ->title('Import gagal')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
        ];
    }
}