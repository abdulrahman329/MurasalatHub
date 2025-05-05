<?php

namespace App\Filament\Resources\CorrespondenceLogResource\Pages;

use App\Filament\Resources\CorrespondenceLogResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCorrespondenceLog extends EditRecord
{
    protected static string $resource = CorrespondenceLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
