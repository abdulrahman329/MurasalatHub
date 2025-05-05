<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Filament\Resources\UserResource\RelationManagers;
use App\Models\User;  // Import the User model
use Filament\Forms;  // Import Filament's Form class
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;  // Import TextInput component from Filament
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\Resource;  // The base Resource class in Filament
use Filament\Tables;  // The Filament Tables package
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;  // Import the TextColumn to render table columns
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class UserResource extends Resource
{
    // The model this resource will interact with
    protected static ?string $model = User::class;

    // The icon to show in the Filament navigation sidebar
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    // Method to define the form schema for creating or updating a User
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                // TextInput for department_id, which is required
                Forms\Components\TextInput::make('department_id')
                    ->required(),  // Make this field required

                // TextInput for the user's name, which is required
                Forms\Components\TextInput::make('name')
                    ->required(),

                // TextInput for email, which is required and must be a valid email
                Forms\Components\TextInput::make('email')
                    ->email()  // This will validate the field as an email
                    ->required(),

                // TextInput for password, which is required
                Forms\Components\TextInput::make('password')
                    ->password()  // The password field will mask input
                    ->required(),

                // TextInput for current_team_id, not required (could be nullable)
                Forms\Components\TextInput::make('current_team_id'),

                // TextInput for profile_photo_path, not required (could be nullable)
                Forms\Components\TextInput::make('profile_photo_path'),
            ]);
    }

    // Method to define the table schema, which will display data in a tabular format
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                // Display the department name related to the user (assuming the User model has a relationship to Department)
                Tables\Columns\TextColumn::make('department.name')
                    ->label('Department'),

                // Display the user's name
                Tables\Columns\TextColumn::make('name'),

                // Display the user's email
                Tables\Columns\TextColumn::make('email'),

                // Hide the password and remember_token fields in the table (sensitive information)
                Tables\Columns\TextColumn::make('password')
                    ->hidden(),
                Tables\Columns\TextColumn::make('remember_token')
                    ->hidden(),

                // Display created_at and updated_at as datetime columns
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime(),
            ])
            ->filters([
                // A filter for the 'name' field, allowing the user to search for users by name
                Tables\Filters\Filter::make('name')
                    ->form([
                        Forms\Components\TextInput::make('name')
                            ->label('Name'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['name'] ?? false) {
                            $query->where('name', 'like', '%' . $data['name'] . '%');  // Filter by name
                        }
                    }),

                // A filter for the 'email' field, allowing the user to search for users by email
                Tables\Filters\Filter::make('email')
                    ->form([
                        Forms\Components\TextInput::make('email')
                            ->label('Email'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['email'] ?? false) {
                            $query->where('email', 'like', '%' . $data['email'] . '%');  // Filter by email
                        }
                    }),

                // A filter for selecting users by department (using a relationship between User and Department models)
                Tables\Filters\SelectFilter::make('department_id')
                    ->relationship('department', 'name')  // Link to the 'department' relationship and show 'name'
                    ->label('Department'),
            ])
            ->actions([
                // The "Edit" action that allows the user to edit a record
                Tables\Actions\EditAction::make(),
                // The "Delete" action that allows the user to delete a record
                Tables\Actions\DeleteAction::make()
            ])
            ->bulkActions([
                // A bulk action to delete multiple records at once
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),  // Bulk delete action
                ]),
            ]);
    }

    // Method for defining relations (not implemented in this case)
    public static function getRelations(): array
    {
        return [
            // This would return an array of relation managers if there were any related resources (not used here)
        ];
    }

    // Method for defining the pages of this resource (index, create, edit)
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),  // The page for listing all users
            'create' => Pages\CreateUser::route('/create'),  // The page for creating a new user
            'edit' => Pages\EditUser::route('/{record}/edit'),  // The page for editing a user
        ];
    }
}
