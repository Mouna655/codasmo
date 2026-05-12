<?php
namespace App\Filament\Resources;

use App\Filament\Resources\LoadingImportResource\Pages;
use App\Models\LoadingSnapshot;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class LoadingImportResource extends Resource
{
    protected static ?string $model           = LoadingSnapshot::class;
    protected static ?string $navigationIcon  = 'heroicon-o-arrow-up-tray';
    protected static ?string $navigationGroup = 'Summary Loading';
    protected static ?string $navigationLabel = 'Upload Data Loading';
    protected static ?int    $navigationSort  = 5;

    public static function canViewAny(): bool
    {
        return in_array(auth()->user()?->role, ['superadmin','operator']);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('upload_date')
                    ->label('Tanggal Upload')
                    ->date('d M Y')->sortable()
                    ->description(fn($record) => $record->data_month_label),

                Tables\Columns\TextColumn::make('original_filename')
                    ->label('File')->limit(40),

                Tables\Columns\TextColumn::make('pen_week_label')
                    ->label('Pen. Label')
                    ->badge()->color('info'),

                Tables\Columns\TextColumn::make('dem_week_label')
                    ->label('Dem. Label')
                    ->badge()->color('warning'),

                Tables\Columns\TextColumn::make('total_rows')
                    ->label('Baris')->badge()->color('success'),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors(['success'=>'success','partial'=>'warning','failed'=>'danger','processing'=>'gray']),

                Tables\Columns\TextColumn::make('uploader.name')
                    ->label('Diupload Oleh')->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Waktu')->dateTime('d M Y H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\Action::make('view')
                    ->label('Lihat Dashboard')
                    ->icon('heroicon-o-eye')
                    ->url(fn($record) => route('dashboard.loading', ['date' => $record->upload_date->format('Y-m-d')])),

                // Edit week labels langsung dari tabel
                Tables\Actions\Action::make('edit_labels')
                    ->label('Edit Week Label')
                    ->icon('heroicon-o-pencil-square')
                    ->form([
                        \Filament\Forms\Components\TextInput::make('pen_week_label')
                            ->label('Label Penalty Week')
                            ->required()
                            ->maxLength(30),
                        \Filament\Forms\Components\TextInput::make('dem_week_label')
                            ->label('Label Demurrage Week')
                            ->required()
                            ->maxLength(30),
                    ])
                    ->fillForm(fn($record) => [
                        'pen_week_label' => $record->pen_week_label,
                        'dem_week_label' => $record->dem_week_label,
                    ])
                    ->action(fn(array $data, $record) => $record->update([
                        'pen_week_label' => $data['pen_week_label'],
                        'dem_week_label' => $data['dem_week_label'],
                    ]))
                    ->modalHeading('Edit Label Kolom Week'),

                Tables\Actions\DeleteAction::make()
                    ->hidden(fn() => !auth()->user()?->isSuperAdmin()),
            ])
            ->defaultSort('upload_date', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLoadingImports::route('/'),
        ];
    }
}