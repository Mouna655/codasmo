<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PoaImportResource\Pages;
use App\Models\PoaSnapshot;
use App\Imports\PoaImport;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Carbon\Carbon;

class PoaImportResource extends Resource
{
    protected static ?string $model            = PoaSnapshot::class;
    protected static ?string $navigationIcon   = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup  = 'POA Dashboard';
    protected static ?string $navigationLabel  = 'Upload Data POA';
    protected static ?int    $navigationSort   = 3;

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'operator']);
    }

    public static function form(Form $form): Form
    {
        return $form->schema([]); // Tidak pakai form edit standard
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('upload_date')
                    ->label('Tanggal Upload')
                    ->date('d M Y')
                    ->sortable()
                    ->description(fn($record) => 'Data tahun ' . $record->data_year),

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('File')
                    ->limit(40)
                    ->tooltip(fn($record) => $record->original_filename),

                Tables\Columns\TextColumn::make('data_year')
                    ->label('Tahun Data')
                    ->badge()
                    ->color('info'),

                Tables\Columns\TextColumn::make('total_rows')
                    ->label('Baris')
                    ->badge()
                    ->color('success'),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Status')
                    ->colors([
                        'success'   => 'success',
                        'partial'   => 'warning',
                        'failed'    => 'danger',
                        'processing'=> 'gray',
                    ]),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Diupload oleh')
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('data_year')
                    ->options(fn() => PoaSnapshot::distinct()->pluck('data_year', 'data_year')->toArray())
                    ->label('Filter Tahun'),
            ])
            ->actions([
                Tables\Actions\Action::make('view_dashboard')
                    ->label('Lihat Dashboard')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('dashboard.poa', [
                        'date' => $record->upload_date->format('Y-m-d'),
                        'year' => $record->data_year,
                    ])),

                Tables\Actions\DeleteAction::make()
                    ->hidden(fn() => !auth()->user()?->isSuperAdmin()),
            ])
            ->defaultSort('upload_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPoaImports::route('/'),
        ];
    }
}