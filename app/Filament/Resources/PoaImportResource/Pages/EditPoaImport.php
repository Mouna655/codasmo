<?php

namespace App\Filament\Resources\PoaImportResource\Pages;

use App\Filament\Resources\PoaImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPoaImport extends EditRecord
{
    protected static string $resource = PoaImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
