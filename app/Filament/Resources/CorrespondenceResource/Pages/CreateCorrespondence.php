<?php

namespace App\Filament\Resources\CorrespondenceResource\Pages;

use App\Filament\Resources\CorrespondenceResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use App\Models\Correspondence_log; // Ensure you import the Correspondence_log model

class CreateCorrespondence extends CreateRecord
{
    protected static string $resource = CorrespondenceResource::class;

    protected function afterCreate(): void
    {
        Correspondence_log::create([
            'correspondence_id' => $this->record->id,
            'user_id' => auth()->id(),
            'action' => $this->record->status,
            'note' => $this->record->notes,
        ]);
    }
}
