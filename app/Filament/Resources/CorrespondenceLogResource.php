<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorrespondenceLogResource\Pages;
use App\Filament\Resources\CorrespondenceLogResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use App\Models\Correspondence_log;
use Filament\Forms\Components\BelongsToSelect;
use Filament\Forms\Components\Select;
use App\Models\User;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\Filter;
use App\Models\Correspondence;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CorrespondenceLogResource extends Resource
{
    // Define the model associated with this resource
    protected static ?string $model = Correspondence_log::class;

    // Set the icon to display in the navigation sidebar
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'المرسلات'; // Group for navigation in the admin panel
    /**
     * Define the form used to create or edit correspondence logs.
     * 
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // Field to select a user (BelongsTo relationship)
                BelongsToSelect::make('user_id') 
                    ->relationship('user', 'name') // Shows user names in the dropdown (uses the 'name' attribute of the User model)
                    ->label('User') // Label for this field
                    ->searchable() // Allow searching for users by name
                    ->required(), // Make this field required

                // Field to select a correspondence (Select field with options from the Correspondence model)
                Select::make('correspondence_id') 
                    ->label('Correspondence Number')
                    ->options(
                        Correspondence::all()->pluck('number', 'id') // Get all correspondence numbers and IDs
                    )
                    ->searchable() // Allow searching in the dropdown
                    ->required(), // Make this field required

                // Text input field for action (e.g., action taken on the correspondence)
                Forms\Components\TextInput::make('action')
                    ->required() // Make this field required
                    ->label('Action'), // Label for this field

                // Textarea for additional notes
                Forms\Components\Textarea::make('note')
                    ->label('Note'), // Label for this field
            ]);
    }

    /**
     * Define the table displaying the correspondence logs.
     * 
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Display the Correspondence ID column, sortable and searchable
                TextColumn::make('correspondence_id')
                    ->label('Correspondence ID')
                    ->sortable() // Allow sorting by this column
                    ->searchable(), // Allow searching in this column
                
                // Display the name of the user who created the correspondence log
                TextColumn::make('user.name')
                    ->label('Created By'), // Label to be shown for this column
                
                // Display the action taken on the correspondence
                TextColumn::make('action')
                    ->label('Action')
                    ->sortable() // Allow sorting by this column
                    ->searchable(), // Allow searching in this column
                
                // Display the note, limit it to 50 characters for readability
                TextColumn::make('note')
                    ->label('Note')
                    ->limit(50), // Limit the displayed note length to 50 characters
                
                // Display the timestamp of when the log was created
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(), // Allow sorting by creation date
            ])
            ->filters([
                // Custom filter to filter by correspondence ID using a text input
                Tables\Filters\Filter::make('correspondence_id')
                    ->form([ // Define the form for the filter
                        Forms\Components\TextInput::make('correspondence_id')
                            ->label('Correspondence ID'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['correspondence_id'] ?? false) {
                            // Apply the filter to the query (search for correspondence ID using the 'like' operator)
                            $query->where('correspondence_id', 'like', '%' . $data['correspondence_id'] . '%');
                        }
                    }),

                // Select filter to filter by "Created By" user
                Tables\Filters\SelectFilter::make('Created By')
                    ->label('Created By') // Label for the filter
                    ->relationship('user', 'name')// Link the filter to the 'user' relationship and display their name
                    ->searchable(), // Allow searching for users

            ])
            ->actions([
                // Action buttons to edit or delete individual correspondence logs
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                // Bulk action to delete multiple correspondence logs at once
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Define any relations to other resources (currently not used).
     * 
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            // Define relationships to other resources, if necessary
        ];
    }

    /**
     * Define the pages for listing, creating, and editing correspondence logs.
     * 
     * @return array
     */
    public static function getPages(): array
    {
        return [
            // Route to the list page
            'index' => Pages\ListCorrespondenceLogs::route('/'),
            // Route to the create page
            'create' => Pages\CreateCorrespondenceLog::route('/create'),
            // Route to the edit page
            'edit' => Pages\EditCorrespondenceLog::route('/{record}/edit'),
        ];
    }
}
