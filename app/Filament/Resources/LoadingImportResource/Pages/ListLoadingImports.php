<?php
namespace App\Filament\Resources\LoadingImportResource\Pages;

use App\Filament\Resources\LoadingImportResource;
use App\Imports\LoadingImport;
use App\Models\LoadingSnapshot;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Support\Facades\Storage;

class ListLoadingImports extends ListRecords
{
    protected static string $resource = LoadingImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('upload_loading')
                ->label('Upload Data Summary Loading')
                ->icon('heroicon-o-arrow-up-tray')
                ->color('primary')
                ->modalHeading('Upload Summary Loading')
                ->modalDescription('Upload file Excel Dummy_Draft_Weekly.xlsx. Sistem hanya membaca sheet "ITM Summary".')
                ->modalWidth('lg')
                ->form([
                    Forms\Components\DatePicker::make('upload_date')
                        ->label('Tanggal Laporan')
                        ->required()
                        ->default(today())
                        ->maxDate(today())
                        ->displayFormat('d M Y')
                        ->native(false)
                        ->helperText('Tanggal ini menentukan "titik waktu" snapshot untuk fitur histori.'),

                    Forms\Components\Select::make('data_month')
                        ->label('Bulan Data')
                        ->required()
                        ->options([
                            1=>'January',2=>'February',3=>'March',4=>'April',
                            5=>'May',6=>'June',7=>'July',8=>'August',
                            9=>'September',10=>'October',11=>'November',12=>'December',
                        ])
                        ->default(now()->month),

                    // ── Label display untuk kolom pen/dem di dashboard ──
                    // Nilai pen & dem SELALU dari kolom CC & CK Excel (fixed)
                    // Label ini hanya keterangan tampilan, misal "Pen. W2"
                    Forms\Components\TextInput::make('pen_week_label')
                        ->label('Label Kolom Penalty')
                        ->default('Pen. W2')
                        ->maxLength(30)
                        ->helperText('Hanya label tampilan. Data pen selalu dari kolom CC (PW2) Excel.'),

                    Forms\Components\TextInput::make('dem_week_label')
                        ->label('Label Kolom Demurrage')
                        ->default('Dem. W2')
                        ->maxLength(30)
                        ->helperText('Hanya label tampilan. Data dem selalu dari kolom CK (DW3) Excel.'),

                    Forms\Components\FileUpload::make('excel_file')
                        ->label('File Excel (.xlsx)')
                        ->required()
                        ->acceptedFileTypes(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'])
                        ->maxSize(20480)
                        ->disk('local')
                        ->directory('loading-imports')
                        ->moveFiles(),
                ])
                ->action(function (array $data): void {
                    $uploadDate = Carbon::parse($data['upload_date'])->format('Y-m-d');
                    $month      = (int) $data['data_month'];
                    $year       = (int) Carbon::parse($data['upload_date'])->year;
                    $monthLabel = Carbon::create($year, $month, 1)->format('F Y');
                    $filePath   = Storage::disk('local')->path($data['excel_file']);

                    $snapshot = LoadingSnapshot::create([
                        'upload_date'       => $uploadDate,
                        'data_month'        => $month,
                        'data_year'         => $year,
                        'data_month_label'  => $monthLabel,
                        // week_number sudah tidak ada
                        'pen_week_label'    => $data['pen_week_label'],
                        'dem_week_label'    => $data['dem_week_label'],
                        'filename'          => $data['excel_file'],
                        'original_filename' => basename($data['excel_file']),
                        'status'            => 'processing',
                        'uploaded_by'       => auth()->id(),
                    ]);

                    try {
                        $importer = new \App\Imports\LoadingImport();
                        $importer->import($filePath, $snapshot->id);

                        \Filament\Notifications\Notification::make()
                            ->title("Import berhasil — {$importer->imported} baris · {$monthLabel}")
                            ->success()->send();

                    } catch (\Exception $e) {
                        $snapshot->update(['status'=>'failed','error_message'=>$e->getMessage()]);
                        \Filament\Notifications\Notification::make()
                            ->title('Import gagal')->body($e->getMessage())->danger()->send();
                    }
                }),
        ];
    }
}