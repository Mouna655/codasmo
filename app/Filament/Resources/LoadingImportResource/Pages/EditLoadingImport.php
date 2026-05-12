<?php

namespace App\Filament\Resources\LoadingImportResource\Pages;

use App\Filament\Resources\LoadingImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditLoadingImport extends EditRecord
{
    protected static string $resource = LoadingImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
