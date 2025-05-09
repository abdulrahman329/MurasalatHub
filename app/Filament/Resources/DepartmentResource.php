<?php

namespace App\Filament\Resources;

use App\Filament\Resources\DepartmentResource\Pages;
use App\Filament\Resources\DepartmentResource\RelationManagers;
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
    // Define the model associated with this resource (Department model)
    protected static ?string $model = Department::class;

    // Set the icon for this resource in the navigation sidebar
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Define the form used for creating or editing a department.
     *
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([  // Define the schema for the form fields

                // Text input field for department name (required)
                TextInput::make('name')
                    ->required(), // The name is a required field
            ]);
    }

    /**
     * Define the table used to display the departments.
     *
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([ // Define the columns displayed in the table

                // Display the department name (sortable and searchable)
                TextColumn::make('name')
                    ->searchable(), // Allows searching by department name
            ])
            ->filters([  // Define the filters available for the table

                // Search filter that allows searching by department name or ID
                Tables\Filters\Filter::make('search')
                    ->form([  // Define the search form input field
                        Forms\Components\TextInput::make('search')
                            ->label('Search for name or id')  // Label for the search field
                            ->placeholder('name'),  // Placeholder text
                    ])
                    ->query(function (Builder $query, array $data) {  // Query to filter departments based on search input
                        if ($search = $data['search'] ?? null) {
                            $query->where(function ($q) use ($search) {
                                // Filter by either department ID or name
                                $q->where('name', 'like', "%{$search}%");
                            });
                        }
                    }),
            ])
            ->actions([  // Define actions that can be taken on a record in the table

                // Action to view contract details
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.departments.view', $record->id))
                    ->openUrlInNewTab(),

                // Action to edit a department record
                Tables\Actions\EditAction::make(),

                // Action to delete a department record
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([  // Define bulk actions for multiple records

                // Bulk delete action for selected department records
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Define any relations to other resources (currently none).
     *
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            // Define relationships to other resources if needed
        ];
    }

    /**
     * Define the pages for this resource (list, create, and edit pages).
     *
     * @return array
     */
    public static function getPages(): array
    {
        return [
            // Route to the list page
            'index' => Pages\ListDepartment::route('/'),
            // Route to view a department
            'view' => Pages\ViewDepartment::route('/{record}/view'), 
        ];
    }
}
