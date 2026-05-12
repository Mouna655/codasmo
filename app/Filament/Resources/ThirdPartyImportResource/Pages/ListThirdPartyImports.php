<?php
namespace App\Filament\Resources\ThirdPartyImportResource\Pages;

use App\Filament\Resources\ThirdPartyImportResource;
use App\Imports\ThirdPartyCoalImport;
use App\Models\ExcelImportLog;
use App\Http\Controllers\ThirdPartyController;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Maatwebsite\Excel\Facades\Excel;

class ListThirdPartyImports extends ListRecords
{
    protected static string $resource = ThirdPartyImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_third_party')
                ->label('Upload 3rd Party Coal')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Upload Data 3rd Party Coal')
                ->modalDescription('Upload file Excel format Dummy_Draft_3rdparty.xlsx. Sheet "3rd Party" akan dibaca.')
                ->modalWidth('lg')
                ->form([
                    Forms\Components\Select::make('year')
                        ->label('Tahun Data')
                        ->options(array_combine(
                            range(now()->year - 2, now()->year + 1),
                            range(now()->year - 2, now()->year + 1)
                        ))
                        ->default(now()->year)
                        ->required()
                        ->native(false),

                    Forms\Components\FileUpload::make('excel_file')
                        ->label('File Excel (.xlsx)')
                        ->required()
                        ->acceptedFileTypes([
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.ms-excel',
                        ])
                        ->maxSize(10240)
                        ->disk('local')
                        ->directory('third-party-imports')
                        ->helperText('Format: Dummy_Draft_3rdparty.xlsx — Sheet "3rd Party" akan dibaca.')
                        ->moveFiles(),
                ])
                ->action(function (array $data): void {
                $year = (int) $data['year'];

                $importer = new ThirdPartyCoalImport($year);

                try {
                    // 🔥 overwrite
                    \App\Models\ThirdPartyCoal::where('year', $year)->delete();

                    // 🔥 import langsung dari disk
                    Excel::import($importer, $data['excel_file'], 'local');

                    ExcelImportLog::create([
                        'report_date'   => "$year-01-01",
                        'filename'      => basename($data['excel_file']),
                        'rows_imported' => $importer->imported,
                        'status'        => count($importer->errors) ? 'partial' : 'success',
                        'uploaded_by'   => auth()->id(),
                    ]);

                    Notification::make()
                        ->title("Import berhasil — {$importer->imported} baris")
                        ->success()
                        ->send();

                } catch (\Exception $e) {
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