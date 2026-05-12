<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExcelImportResource\Pages;
use App\Models\ExcelImportLog;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ExcelImportResource extends Resource
{
    protected static ?string $model            = ExcelImportLog::class;
    protected static ?string $navigationIcon   = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup  = 'Excel Daily Production';
    protected static ?string $navigationLabel  = 'Upload Data Excel';
    protected static ?int    $navigationSort   = 2;

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'operator']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('report_date')
                    ->label('Tanggal Laporan')
                    ->date('d M Y')
                    ->sortable(),

                Tables\Columns\TextColumn::make('filename')
                    ->label('File')
                    ->limit(40),

                Tables\Columns\BadgeColumn::make('file_type')
                    ->label('Tipe')
                    ->colors([
                        'success' => 'xlsx',
                        'info' => 'csv',
                    ])
                    ->formatStateUsing(fn($state) => strtoupper($state)),

                Tables\Columns\TextColumn::make('rows_imported')
                    ->label('Berhasil')
                    ->badge()
                    ->color('success'),

                Tables\Columns\TextColumn::make('errors_count')
                    ->label('Error')
                    ->badge()
                    ->color(fn($state) => $state > 0 ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn($state) => match($state) {
                        'success' => 'success',
                        'partial' => 'warning',
                        'failed'  => 'danger',
                        default   => 'gray',
                    }),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Diupload oleh'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view_errors')
                    ->label('Detail')
                    ->icon('heroicon-o-eye')
                    ->visible(fn(ExcelImportLog $record) => $record->errors_count > 0)
                    ->url(fn(ExcelImportLog $record) => static::getUrl('view-details', ['record' => $record->id])),

                Tables\Actions\DeleteAction::make()
                    ->hidden(fn() => !auth()->user()->isSuperAdmin()),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExcelImports::route('/'),
            'view-details' => Pages\ViewImportDetails::route('/{record}/details'),
        ];
    }
}