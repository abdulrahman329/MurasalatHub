<?php

namespace App\Filament\Resources\CorrespondenceResource\Pages;

use App\Filament\Resources\CorrespondenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditCorrespondence extends EditRecord
{
    protected static string $resource = CorrespondenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
