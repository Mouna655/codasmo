<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ShipmentImportResource\Pages;
use App\Models\ShipmentSnapshot;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class ShipmentImportResource extends Resource
{
    protected static ?string $model           = ShipmentSnapshot::class;
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Summary Shipment';
    protected static ?string $navigationLabel = 'Upload Data Shipment';
    protected static ?int    $navigationSort  = 7;

    public static function canViewAny(): bool {
        return in_array(auth()->user()?->role, ['superadmin','operator']);
    }

    public static function table(Table $table): Table {
        return $table->columns([
            Tables\Columns\TextColumn::make('upload_date')
                ->label('Tanggal Upload')->date('d M Y')->sortable()
                ->description(fn($record) => $record->total_rows.' baris dari 6 sheet'),
            Tables\Columns\TextColumn::make('original_filename')
                ->label('File')->limit(40),
            Tables\Columns\TextColumn::make('total_rows')
                ->label('Total Baris')->badge()->color('success'),
            Tables\Columns\BadgeColumn::make('status')
                ->colors(['success'=>'success','partial'=>'warning',
                          'failed'=>'danger','processing'=>'gray']),
            Tables\Columns\TextColumn::make('uploader.name')
                ->label('Diupload Oleh')->toggleable(),
            Tables\Columns\TextColumn::make('created_at')
                ->label('Waktu')->dateTime('d M Y H:i')->sortable(),
        ])
        ->actions([
            Tables\Actions\Action::make('view')
                ->label('Lihat Dashboard')
                ->icon('heroicon-o-eye')
                ->url(fn($record) => route('public.shipment', [
                    'date' => $record->upload_date->format('Y-m-d')
                ])),
            Tables\Actions\DeleteAction::make()
                ->hidden(fn() => !auth()->user()?->isSuperAdmin()),
        ])
        ->defaultSort('upload_date','desc');
    }

    public static function getPages(): array {
        return ['index' => Pages\ListShipmentImports::route('/')];
    }
}