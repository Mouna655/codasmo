<?php

namespace App\Filament\Resources\ThirdPartyImportResource\Pages;

use App\Filament\Resources\ThirdPartyImportResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditThirdPartyImport extends EditRecord
{
    protected static string $resource = ThirdPartyImportResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
