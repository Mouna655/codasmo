<?php

namespace App\Filament\Resources\ExcelImportResource\Pages;

use App\Filament\Resources\ExcelImportResource;
use App\Models\{ExcelImportLog, ImportDetailLog};
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;

class ViewImportDetails extends ViewRecord implements HasTable
{
    use InteractsWithTable;

    protected static string $resource = ExcelImportResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->modelLabel('error')
            ->pluralModelLabel('errors')
            ->query(
                ImportDetailLog::where('import_log_id', $this->record->id)
                    ->orderBy('row_number')
            )
            ->columns([
                Tables\Columns\TextColumn::make('row_number')
                    ->label('Baris')
                    ->sortable()
                    ->width('80px'),

                Tables\Columns\TextColumn::make('site_code')
                    ->label('Site')
                    ->searchable()
                    ->width('100px'),

                Tables\Columns\TextColumn::make('sub_site_code')
                    ->label('Sub-Site')
                    ->searchable()
                    ->width('100px'),

                Tables\Columns\BadgeColumn::make('error_field')
                    ->label('Field')
                    ->colors([
                        'danger' => 'site',
                        'danger' => 'sub_site',
                        'warning' => 'fc_production_daily',
                        'warning' => 'fc_production_mtd',
                        'warning' => 'port_stock_yard_daily',
                        'warning' => 'port_stock_yard_mtd',
                        'info' => 'coal_winning_daily',
                        'info' => 'rom_stock',
                    ])
                    ->default('-'),

                Tables\Columns\TextColumn::make('error_message')
                    ->label('Detail Error')
                    ->wrap()
                    ->searchable(),

                Tables\Columns\BadgeColumn::make('error_type')
                    ->label('Tipe')
                    ->colors([
                        'danger' => 'format_error',
                        'danger' => 'header_mismatch',
                        'danger' => 'not_found',
                        'warning' => 'validation_error',
                        'warning' => 'type_error',
                        'info' => 'business_logic',
                    ])
                    ->formatStateUsing(
                        fn($state) => match($state) {
                            'format_error' => 'Format Error',
                            'header_mismatch' => 'Header Tidak Sesuai',
                            'not_found' => 'Data Tidak Ditemukan',
                            'validation_error' => 'Validasi Error',
                            'type_error' => 'Type Error',
                            'business_logic' => 'Business Logic',
                            default => $state,
                        }
                    ),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i:s')
                    ->sortable(),
            ])
            ->defaultSort('row_number')
            ->paginated([10, 25, 50, 100]);
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('export_errors')
                ->label('Export Errors as CSV')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function() {
                    $errors = ImportDetailLog::where('import_log_id', $this->record->id)
                        ->orderBy('row_number')
                        ->get();

                    $csv = "Row\tSite\tSub-Site\tField\tError Message\tType\tTime\n";
                    
                    foreach ($errors as $error) {
                        $csv .= implode("\t", [
                            $error->row_number,
                            $error->site_code ?? '-',
                            $error->sub_site_code ?? '-',
                            $error->error_field ?? '-',
                            $error->error_message,
                            $error->error_type,
                            $error->created_at->format('Y-m-d H:i:s'),
                        ]) . "\n";
                    }

                    return response()->streamDownload(
                        fn() => print($csv),
                        "import-errors-{$this->record->id}.csv"
                    );
                }),

            Actions\Action::make('download_original')
                ->label('Download Original File')
                ->icon('heroicon-o-arrow-down-tray')
                ->action(function() {
                    $filePath = storage_path('app/excel-imports/' . $this->record->filename);
                    if (file_exists($filePath)) {
                        return response()->download($filePath);
                    }
                    
                    \Filament\Notifications\Notification::make()
                        ->title('File tidak ditemukan')
                        ->danger()
                        ->send();
                }),

            Actions\EditAction::make()
                ->hidden(),

            Actions\DeleteAction::make()
                ->hidden(fn() => !auth()->user()->isSuperAdmin()),
        ];
    }
}
