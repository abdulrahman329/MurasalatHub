<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorrespondenceLogResource\Pages;
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
use App\Models\Correspondence;
use Illuminate\Database\Eloquent\Builder;

class CorrespondenceLogResource extends Resource
{
    protected static ?string $model = Correspondence_log::class;

    protected static ?string $navigationIcon = 'heroicon-o-clipboard-document-list';

    // ✅ Arabic navigation and labels
    protected static ?string $navigationLabel = 'سجل المراسلات';
    protected static ?string $modelLabel = 'سجل المراسلة';
    protected static ?string $pluralModelLabel = 'سجلات المراسلات';

    protected static ?string$navigationGroup = 'المراسلات';

    protected static ?int $navigationSort = 0;

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

                Forms\Components\TextInput::make('action')
                    ->label('الإجراء')
                    ->required(),

                Forms\Components\Textarea::make('note')
                    ->label('ملاحظة'),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('correspondence_id')
                    ->label('رقم المراسلة')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('user.name')
                    ->label('أنشأ بواسطة'),

                TextColumn::make('action')
                    ->label('الإجراء')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('note')
                    ->label('Note')
                    ->limit(50), // Limit the displayed note length to 50 characters
                
                // Display the timestamp of when the log was created
                TextColumn::make('created_at')
                    ->label('Created At')
                    ->sortable(), // Allow sorting by creation date
            ])
            ->filters([
                Tables\Filters\Filter::make('correspondence_id')
                    ->form([
                        Forms\Components\TextInput::make('correspondence_id')
                            ->label('رقم المراسلة'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['correspondence_id'] ?? false) {
                            $query->where('correspondence_id', 'like', '%' . $data['correspondence_id'] . '%');
                        }
                    }),

                Tables\Filters\SelectFilter::make('user_id')
                    ->label('أنشأ بواسطة')
                    ->relationship('user', 'name')
                    ->searchable(),
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

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
{
    return false;  // Disables the "New" button
}


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
