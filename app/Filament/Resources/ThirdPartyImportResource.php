<?php
namespace App\Filament\Resources;

use App\Filament\Resources\ThirdPartyImportResource\Pages;
use App\Models\ExcelImportLog;
use App\Imports\ThirdPartyCoalImport;
use Filament\Forms;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Maatwebsite\Excel\Facades\Excel;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use App\Http\Controllers\ThirdPartyController;
use App\Events\DashboardUpdated;

class ThirdPartyImportResource extends Resource
{
    protected static ?string $model           = ExcelImportLog::class;
    // Pakai model yang sama dengan ExcelImportLog tapi slug berbeda
    protected static ?string $slug            = 'third-party-imports';
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Third Party Coal';
    protected static ?string $navigationLabel = 'Upload 3rd Party Coal';
    protected static ?int    $navigationSort  = 3;

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin', 'operator']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn($query) => $query->where('filename', 'like', '%3rdparty%')
                ->orWhere('filename', 'like', '%3rd_party%')
                ->orWhere('filename', 'like', '%third%'))
            ->columns([
                Tables\Columns\TextColumn::make('report_date')->label('Tahun')->date('Y')->sortable(),
                Tables\Columns\TextColumn::make('filename')->label('File')->limit(40),
                Tables\Columns\TextColumn::make('rows_imported')->label('Baris')->badge()->color('success'),
                Tables\Columns\TextColumn::make('status')->label('Status')->badge()
                    ->color(fn($s) => match($s) { 'success'=>'success','partial'=>'warning','failed'=>'danger', default=>'gray' }),
                Tables\Columns\TextColumn::make('uploader.name')->label('Oleh'),
                Tables\Columns\TextColumn::make('created_at')->label('Waktu')->dateTime('d M Y H:i')->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->actions([
                Tables\Actions\Action::make('view_dashboard')
                    ->label('Lihat Dashboard')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('dashboard.third-party', [
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
            'index' => Pages\ListThirdPartyImports::route('/'),
        ];
    }
}