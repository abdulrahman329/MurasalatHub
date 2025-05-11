<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Models\Department;
use Filament\Forms\Components\TextInput;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Filament\Tables\Columns\TextColumn;

class DepartmentResource extends Resource
{
    protected static ?string $model = Department::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';

    // ✅ Arabic labels
    protected static ?string $navigationLabel = 'الأقسام';
    protected static ?string $modelLabel = 'قسم';
    protected static ?string $pluralModelLabel = 'الأقسام';

    protected static ?string$navigationGroup = "إعدادات النظام";


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                    ->label('اسم القسم')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([ // Define the columns displayed in the table

                // Display the department ID (sortable)
                TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                TextColumn::make('name')
                    ->sortable()  // Allows sorting by name
                    ->searchable(), // Allows searching by department name
            ])
            ->filters([
                Tables\Filters\Filter::make('search')
                    ->form([
                        Forms\Components\TextInput::make('search')
                            ->label('Search for name or id')  // Label for the search field
                            ->placeholder('name or department id'),  // Placeholder text
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($search = $data['search'] ?? null) {
                            $query->where(function ($q) use ($search) {
                                // Filter by either department ID or name
                                $q->where('id', 'like', "%{$search}%")
                                    ->orWhere('name', 'like', "%{$search}%");
                            });
                        }
                    }),
            ])
            ->actions([  // Define actions that can be taken on a record in the table

                // Action to edit a department record
                Tables\Actions\EditAction::make(),

                // Action to delete a department record
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف جماعي'),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function getPages(): array
    {
        return [
            // Route to the list page
            'index' => Pages\ListDepartments::route('/'),

            // Route to the create page
            'create' => Pages\CreateDepartment::route('/create'),

            // Route to the edit page
            'edit' => Pages\EditDepartment::route('/{record}/edit'),
        ];
    }
}
