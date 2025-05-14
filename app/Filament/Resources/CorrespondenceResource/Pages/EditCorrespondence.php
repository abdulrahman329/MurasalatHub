<?php

namespace App\Filament\Resources\CorrespondenceResource\Pages;

use App\Filament\Resources\CorrespondenceResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use App\Models\Correspondence_log; // Ensure the correct namespace is used

class EditCorrespondence extends EditRecord
{
    protected static string $resource = CorrespondenceResource::class;

    protected function afterSave(): void
    {
        // Find the existing CorrespondenceLog by correspondence_id
        $log = Correspondence_log::where('correspondence_id', $this->record->id)->first();

        if ($log) {
            // Update the existing log entry
            $log->update([
                'action' => $this->record->status, // Update the action to the new status
                'note' => $this->record->notes,   // Update the note to the new notes
            ]);
        } else {
            // If no log exists, create a new one (fallback)
            Correspondence_log::create([
                'correspondence_id' => $this->record->id,
                'user_id' => $this->record->created_by,
                'action' => $this->record->status,
                'note' => $this->record->notes,
            ]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
