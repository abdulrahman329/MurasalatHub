<?php

namespace App\Filament\Resources;

use App\Filament\Resources\UserResource\Pages;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class UserResource extends Resource
{
    protected static ?string $model = User::class;

    protected static ?string $navigationIcon = 'heroicon-o-user';
    protected static ?string $navigationLabel = 'المستخدمون';
    protected static ?string $modelLabel = 'مستخدم';
    protected static ?string $pluralModelLabel = 'المستخدمون';

    protected static ?string$navigationGroup = "إعدادات النظام";



    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')
                ->label('اسم المستخدم')
                ->required(),

            Forms\Components\TextInput::make('email')
                ->label('البريد الإلكتروني')
                ->email()
                ->required(),

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

                // TextInput for current_team_id (nullable, not required)
                Forms\Components\TextInput::make('current_team_id'),

                // TextInput for profile_photo_path (nullable, not required)
                Forms\Components\TextInput::make('profile_photo_path'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('المعرف'),

                Tables\Columns\TextColumn::make('name')
                    ->label('اسم المستخدم'),

                Tables\Columns\TextColumn::make('email')
                    ->label('البريد الإلكتروني'),

                Tables\Columns\TextColumn::make('department.name')
                    ->label('القسم'),

                // Hide the password and remember_token fields (sensitive information)
                Tables\Columns\TextColumn::make('password')->hidden(),
                Tables\Columns\TextColumn::make('remember_token')->hidden(),

                // Display created_at and updated_at as datetime columns
                Tables\Columns\TextColumn::make('created_at')->dateTime(),
                Tables\Columns\TextColumn::make('updated_at')->dateTime(),
            ])
            ->filters([  // Define filters available for the table

                // Search filter that allows searching by user ID, name, or email
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('search')
                            ->label('ابحث')
                            ->placeholder('المعرف أو الاسم أو البريد الإلكتروني'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($search = $data['search'] ?? null) {
                            $query->where(function ($q) use ($search) {
                                $q->where('id', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%");
                            });
                        }
                    }),

                // A filter for selecting users by department
                Tables\Filters\SelectFilter::make('department_id')
                    ->label('Department')
                    ->relationship('department', 'name')  // Link to the 'department' relationship and show 'name'
                    ->searchable(),  // Make the department filter searchable
            ])
            ->actions([  // Define actions that can be taken on a record

                // Action to edit a user record
                Tables\Actions\EditAction::make(),

                // Action to delete a user record
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([  // Define bulk actions for multiple records

                // Bulk action to delete multiple user records
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()
                        ->label('حذف جماعي'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListUsers::route('/'),  // The page for listing all users
            'create' => Pages\CreateUser::route('/create'),  // The page for creating a new user
            'edit' => Pages\EditUser::route('/{record}/edit'),  // The page for editing a user
        ];
    }
}
