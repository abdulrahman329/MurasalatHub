<?php

namespace App\Filament\Resources\ContractsResource\Pages;

use App\Filament\Resources\ContractsResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditContracts extends EditRecord
{
    protected static string $resource = ContractsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
