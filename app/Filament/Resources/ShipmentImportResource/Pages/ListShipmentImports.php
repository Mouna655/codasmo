<?php
namespace App\Filament\Resources\ShipmentImportResource\Pages;

use App\Filament\Resources\ShipmentImportResource;
use App\Imports\ShipmentImport;
use App\Models\ShipmentSnapshot;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListShipmentImports extends ListRecords
{
    protected static string $resource = ShipmentImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_shipment')
                ->label('Upload Data Shipment')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Upload Summary Shipment')
                ->modalDescription(
                    'Upload file Excel Dummy_Draft_Weekly.xlsx. ' .
                    'Sistem membaca sheet Month 1 s/d Month 6 secara otomatis.'
                )
                ->modalWidth('lg')
                ->form([
                    Forms\Components\DatePicker::make('upload_date')
                        ->label('Tanggal Laporan')
                        ->required()->default(today())->maxDate(today())
                        ->displayFormat('d M Y')->native(false)
                        ->helperText('Tanggal ini menentukan snapshot. User bisa pilih tanggal historis untuk melihat data lama.'),

                    Forms\Components\FileUpload::make('excel_file')
                        ->label('File Excel (.xlsx)')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
                        ])
                        ->maxSize(30720)
                        ->disk('local')
                        ->directory('shipment-imports')
                        ->helperText('Sheet "Month 1" s/d "Month 6" akan dibaca sekaligus.')
                        ->moveFiles(),
                ])
                ->action(function (array $data): void {
                    $uploadDate = Carbon::parse($data['upload_date'])->format('Y-m-d');
                    $filePath   = Storage::disk('local')->path($data['excel_file']);

                    $snapshot = ShipmentSnapshot::create([
                        'upload_date'       => $uploadDate,
                        'filename'          => $data['excel_file'],
                        'original_filename' => basename($data['excel_file']),
                        'status'            => 'processing',
                        'uploaded_by'       => auth()->id(),
                    ]);

                    try {
                        $importer = new ShipmentImport();
                        $importer->import($filePath, $snapshot->id);

                        Notification::make()
                            ->title("Import berhasil — {$importer->imported} baris dari 6 sheet")
                            ->success()->send();

                    } catch (\Exception $e) {
                        $snapshot->update([
                            'status'        => 'failed',
                            'error_message' => $e->getMessage(),
                        ]);
                        Notification::make()
                            ->title('Import gagal')
                            ->body($e->getMessage())
                            ->danger()->send();
                    }
                }),
        ];
    }
}