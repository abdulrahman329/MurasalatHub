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

class ContractsResource extends Resource
{
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    // ✅ Arabic navigation labels
    protected static ?string $navigationLabel = 'العقود';
    protected static ?string $modelLabel = 'عقد';
    protected static ?string $pluralModelLabel = 'العقود';

    protected static ?string$navigationGroup = 'العقود';

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
            ->filters([
                Tables\Filters\Filter::make('search')
                    ->form([
                        TextInput::make('search')
                            ->label('بحث عن العنوان أو الطرف أو نوع العقد')
                            ->placeholder('العنوان أو الطرف أو نوع العقد'),
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

                Tables\Filters\Filter::make('start_date')
                    ->label('تاريخ البدء بعد')
                    ->form([
                        DatePicker::make('start_date')->label('تاريخ البدء'),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $data['start_date'] ? $query->whereDate('start_date', '>=', $data['start_date']) : $query
                    ),

                Tables\Filters\Filter::make('end_date')
                    ->label('تاريخ الانتهاء قبل')
                    ->form([
                        DatePicker::make('end_date')->label('تاريخ الانتهاء'),
                    ])
                    ->query(fn (Builder $query, array $data) =>
                        $data['end_date'] ? $query->whereDate('end_date', '<=', $data['end_date']) : $query
                    ),

                Tables\Filters\SelectFilter::make('responsible_user_id')
                    ->label('المسؤول')
                    ->relationship('responsibleUser', 'name')
                    ->searchable(),
            ])
            ->actions([  // Define actions that can be performed on individual records
                Tables\Actions\EditAction::make(),  // Action to edit a contract
                Tables\Actions\DeleteAction::make(),  // Action to delete a contract
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف الكل'),
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
            'index' => Pages\ListContracts::route('/'),  // List page for contracts
            'create' => Pages\CreateContracts::route('/create'),  // Create page for new contracts
            'edit' => Pages\EditContracts::route('/{record}/edit'),  // Edit page for editing existing contracts
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
