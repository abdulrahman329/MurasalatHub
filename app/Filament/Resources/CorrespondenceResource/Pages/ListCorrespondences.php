<?php

namespace App\Filament\Resources\CorrespondenceResource\Pages;

use App\Filament\Resources\CorrespondenceResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListCorrespondences extends ListRecords
{
    protected static string $resource = CorrespondenceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
