<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\{TextInput, DatePicker, FileUpload, Select};
use Filament\Tables\Columns\{TextColumn};
use Filament\Tables\Actions\{EditAction, DeleteBulkAction};
use Filament\Tables\Filters;
use Filament\Tables\Filters\DateFilter;
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\ContractsResource\Pages\{ListContracts, CreateContracts, EditContracts, ViewContracts};
use App\Filament\Resources\ContractsResource\Pages;
use App\Filament\Resources\ContractsResource\RelationManagers;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use App\Models\Contract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContractsResource extends Resource
{
    // Define the model that this resource corresponds to (Contract model)
    protected static ?string $model = Contract::class;

    // Set the navigation icon for this resource in Filament's admin panel
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Define the form for creating or editing a contract.
     * 
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form->schema([  // Define the fields for the contract form
            TextInput::make('title')->required(),  // Title of the contract (required)
            Select::make('contract_type')
                ->label('Contract Type')
                ->options([
                    'Procurement Contract' => 'Procurement Contract',
                    'Service Contract' => 'Service Contract',
                    'Consulting Contract' => 'Consulting Contract',
                    'Lease Contract' => 'Lease Contract',
                    'Maintenance Contract' => 'Maintenance Contract',
                    'Sales Contract' => 'Sales Contract',
                    'Employment Contract' => 'Employment Contract',
                ])
                ->required(),

                DatePicker::make('start_date')  // Start date of the contract
                ->required()
                ->before('end_date')  // Ensure that start date is before the end date
                ->minDate(now()),  // Ensure start date is not before today

            DatePicker::make('end_date')  // End date of the contract
                ->required()
                ->after('start_date'),  // Ensure that end date is after the start date

                Select::make('responsible_user_id')  // Select input for the responsible user
                ->label('Responsible User')
                ->relationship('responsibleUser', 'name')  // Relationship to the 'responsibleUser' model
                ->searchable()  // Make the select input searchable
                ->required(),  // The responsible user is a required field

        // Accept more than just PDF and image files (e.g., Word, Excel, PowerPoint, ZIP)
        FileUpload::make('file')
            ->directory('contracts')
            ->acceptedFileTypes([
            'application/pdf',
            'image/*',
            'application/msword',
            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'application/vnd.ms-excel',
            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'application/vnd.ms-powerpoint',
            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'application/zip',
            'application/x-7z-compressed',
            'application/x-rar-compressed',
            'application/x-tar',
            ])
            ->maxSize(10240),
        ]);
    }

    /**
     * Define the table that displays the list of contracts.
     * 
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([  // Define the columns for the contracts table
                TextColumn::make('title')->sortable()->searchable(),  // Title column, sortable and searchable
                TextColumn::make('responsibleUser.name')->label('Responsible'),  // Responsible user column
                TextColumn::make('contract_type'),  // Contract type column
                TextColumn::make('file')  // File column
                ->label('File')
                ->getStateUsing(function ($record) {
                // Add custom logic for determining if a file exists
                if (empty($record->file)) {
                    return '❌ No File';
                }

                    // Return custom file info, e.g., filename or extension
                    return '✅ ' . pathinfo($record->file, PATHINFO_EXTENSION) . ' File';
                }),              
                TextColumn::make('start_date')->sortable()->date(),  // Start date column, sortable and displayed as date
                TextColumn::make('end_date')->sortable()->date(),  // End date column, sortable and displayed as date   
            ])
            ->filters([  // Define filters to apply to the table
                Tables\Filters\Filter::make('search')  // Search filter by title, party name, or contract type
                    ->form([
                        Forms\Components\TextInput::make('search')
                            ->label('Search for Title or Party Name or Contract Type')
                            ->placeholder('Title or Party Name or Contract Type'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($search = $data['search'] ?? null) {
                            $query->where(function ($q) use ($search) {
                                $q->where('contract_type', 'like', "%{$search}%")
                                  ->orWhere('party_name', 'like', "%{$search}%")
                                  ->orWhere('title', 'like', "%{$search}%");
                            });
                        }
                    }),

                Tables\Filters\Filter::make('start_date')  // Filter contracts by start date
                    ->label('Start Date After')
                    ->form([
                        Forms\Components\DatePicker::make('start_date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['start_date'] ?? null) {
                            $query->whereDate('start_date', '>=', $data['start_date']);
                        }
                    }),

                Tables\Filters\Filter::make('end_date')  // Filter contracts by end date
                    ->label('End Date Before')
                    ->form([
                        Forms\Components\DatePicker::make('end_date'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($data['end_date'] ?? null) {
                            $query->whereDate('end_date', '<=', $data['end_date']);
                        }
                    }),
                
                Tables\Filters\SelectFilter::make('responsible_user_id')  // Filter contracts by responsible user
                    ->label('Responsible User')
                    ->relationship('responsibleUser', 'name')
                    ->searchable(), 
            ])
            ->actions([  // Define actions that can be performed on individual records
                
                // Action to view contract details
                Tables\Actions\Action::make('view')
                    ->label('View')
                    ->icon('heroicon-o-eye')
                    ->url(fn ($record) => route('filament.admin.resources.contracts.view', $record->id))
                    ->openUrlInNewTab(),

                Tables\Actions\EditAction::make(),  // Action to edit a contract

                Tables\Actions\DeleteAction::make(),  // Action to delete a contract

            ])
            ->bulkActions([  // Define bulk actions that can be performed on selected records
                Tables\Actions\BulkActionGroup::make([  // Group for bulk actions
                    Tables\Actions\DeleteBulkAction::make(),  // Bulk delete action
                ]),
            ]);
    }

    /**
     * Define any relationships for the contract resource.
     * Currently, this method is not being used.
     * 
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            // Define relationships if any
        ];
    }

    /**
     * Define the pages for listing, creating, and editing contracts.
     * 
     * @return array
     */
    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),  // List page for contracts
            'create' => Pages\CreateContracts::route('/create'),  // Create page for new contracts
            'edit' => Pages\EditContracts::route('/{record}/edit'),  // Edit page for editing existing contracts
            'view' => Pages\ViewContracts::route('/{record}/view'),  // View page for viewing contract details 
        ];
    }

    public static function getModelLabel(): string
{
    return __('عقد');
}

public static function getPluralModelLabel(): string
{
    return __('عقود');
}

}
