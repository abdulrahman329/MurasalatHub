<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;  
use Filament\Forms; 
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;  
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;  
use Filament\Tables;  
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;  
use Filament\Forms\Components\Select;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    // The model this resource will interact with
    protected static ?string $model = User::class;

    // The icon to show in the Filament navigation sidebar
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Define the form schema for creating or updating a User.
     *
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([  // Define the schema for the form fields

                // TextInput for the user's name, which is required
                Forms\Components\TextInput::make('name')
                    ->required(),  // Name is a required field

                // TextInput for email, which is required and must be a valid email
                Forms\Components\TextInput::make('email')
                    ->email()  // This will validate the field as an email
                    ->required(),  // Email is a required field

                // TextInput for password, which is required and will mask input
                Forms\Components\TextInput::make('password')
                    ->password()  // The password field will mask input
                    ->required(),  // Password is a required field

                // Select input for department, which is required
                Select::make('Department_id')
                    ->label('Department name')  // Label for the department field
                    ->relationship('department', 'name')  // Relationship to the 'department' model
                    ->searchable()  // Make the select input searchable
                    ->required(),  // Department is a required field
            ]);
    }

    /**
     * Define the table schema, which will display user data in a tabular format.
     *
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([  // Define the columns to be displayed in the table

                // Display the user's ID
                Tables\Columns\TextColumn::make('id'),

                // Display the user's name
                Tables\Columns\TextColumn::make('name'),

                // Display the user's email
                Tables\Columns\TextColumn::make('email'),

                // Display the department name related to the user
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department'),

            ])
            ->filters([  // Define filters available for the table

                // Search filter that allows searching by user ID, name, or email
                Tables\Filters\Filter::make('search')
                    ->form([  // Define the search form input field
                        Forms\Components\TextInput::make('search')
                            ->label('Search for id or name or email')  // Label for the search field
                            ->placeholder('id or name or email'),  // Placeholder text
                    ])
                    ->query(function (Builder $query, array $data) {  // Query to filter users based on search input
                        if ($search = $data['search'] ?? null) {
                            $query->where(function ($q) use ($search) {
                                // Filter by user ID, name, email, or department name
                                $q->where('id', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%")
                                    ->orWhereHas('department', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%");
                                    });
                            });
                        }
                    }),

                // A filter for selecting users by department
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')  // Link to the 'department' relationship and show 'name'
                    ->searchable(),  // Make the department filter searchable

                // A filter for recently created users
                Tables\Filters\Filter::make('recent')
                    ->label('Recently Created')
                    ->query(fn (Builder $query) => $query->where('created_at', '>=', now()->subDays(7))),
            ])
            ->actions([  // Define actions that can be taken on a record

                // Action to view contract details
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.users.view', $record->id))
                    ->openUrlInNewTab(),

                // Action to edit a user record
                Tables\Actions\EditAction::make(),

                // Action to delete a user record
                Tables\Actions\DeleteAction::make(),

            ])
            ->bulkActions([  // Define bulk actions for multiple records

                // Bulk action to delete multiple user records
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),  // Bulk delete action
                ]),
            ]);
    }

    /**
     * Define the relations for this resource (if any).
     *
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            // Define relationships to other resources if needed (none used here)
        ];
    }

    /**
     * Define the pages for this resource (index, create, and edit).
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUser::route('/'),  // The page for listing all users
            //'create' => Pages\CreateUser::route('/create'),  // The page for creating a new user
            //'edit' => Pages\EditUser::route('/{record}/edit'),  // The page for editing a user
            'view' => Pages\ViewUser::route('/{record}/view'),  // The page for viewing a user's details
        ];
    }
}
