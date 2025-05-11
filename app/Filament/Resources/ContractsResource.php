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
use App\Filament\Resources\ContractsResource\Pages\{ListContracts, CreateContracts, EditContracts};
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

            TextInput::make('contract_type')->required(),  // Type of contract (required)
    
            TextInput::make('party_name')->required(),  // Name of the contracting party (required)

            Select::make('responsible_user_id')  // Select input for the responsible user
                ->label('Responsible User')
                ->relationship('responsibleUser', 'name')  // Relationship to the 'responsibleUser' model
                ->searchable()  // Make the select input searchable
                ->required(),  // The responsible user is a required field

                DatePicker::make('start_date')  // Start date of the contract
                ->required()
                ->before('end_date'),  // Ensure that start date is before the end date

            DatePicker::make('end_date')  // End date of the contract
                ->required()
                ->after('start_date'),  // Ensure that end date is after the start date
        
            FileUpload::make('file')  // File upload for attaching the contract file
                ->directory('contracts')  // Store the file in the 'contracts' folder
                ->acceptedFileTypes(['application/pdf', 'image/*'])  // Accept PDF and image files
                ->maxSize(10240),  // Limit file size to 10MB
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
                TextColumn::make('party_name'),  // Party name column
                TextColumn::make('file')  // File column
                    ->label('File')
                    ->formatStateUsing(function ($state) {  // Custom formatting for the file column
                        if ($state === null) {
                            return '❌ No File';  // No file uploaded
                        } else if (isset($state)) {
                            return '✅ File ';  // File uploaded
                        }
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
        ];
    }
}
