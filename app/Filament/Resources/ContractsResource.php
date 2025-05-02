<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\{TextInput, DatePicker, FileUpload, Select};
use Filament\Tables\Columns\{TextColumn};
use Filament\Tables\Actions\{EditAction, DeleteBulkAction};
use Filament\Tables\Filters;
use Filament\Tables\Filters\SelectFilter;
// use Filament\Tables\Filters\DateFilter; // Removed: Not available in Filament core
use Filament\Tables\Actions\BulkActionGroup;
use Filament\Tables\Actions\DeleteAction;
use Filament\Tables\Actions\BulkAction;
use Filament\Tables\Actions\ActionGroup;
use Filament\Tables\Actions\Action;
use App\Filament\Resources\ContractsResource\Pages\{ListContracts, CreateContracts, EditContracts};
use App\Filament\Resources\ContractsResource\Pages;
use App\Filament\Resources\ContractsResource\RelationManagers;
use App\Models\Contracts;
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
    protected static ?string $model = Contract::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')->required(),
            TextInput::make('content')->required(),
    
            DatePicker::make('start_date')->required(),
            DatePicker::make('end_date')->required(),
    
            TextInput::make('contract_type')->required(),
    
            TextInput::make('party_name')->required(),
    
            FileUpload::make('file')
                ->directory('contracts') // Folder in storage/app/public
                ->acceptedFileTypes(['application/pdf', 'image/*']) // Customize types
                ->maxSize(10240), // Max 10MB
    
            Select::make('responsible_user_id')
                ->label('Responsible User')
                ->relationship('responsibleUser', 'name') // assumes User model has 'name'
                ->searchable()
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                    TextColumn::make('title')->sortable()->searchable(),
                    TextColumn::make('start_date')->sortable()->date(), // Format as date
                    TextColumn::make('end_date')->sortable()->date(),   // Format as date
                    TextColumn::make('contract_type'),
                    TextColumn::make('party_name'),
                    TextColumn::make('responsibleUser.name')->label('Responsible'),
                    TextColumn::make('content')->limit(50), // Preview part of the content
                ])
            ->filters([
                Tables\Filters\SelectFilter::make('contract_type')
                    ->label('Contract Type')
                    ->options(
                        Contract::query()->distinct()->pluck('contract_type', 'contract_type')->toArray()
                    ),
                    
                        Tables\Filters\Filter::make('start_date')
                            ->label('Start Date After')
                            ->form([
                                Forms\Components\DatePicker::make('start_date'),
                            ])
                            ->query(function ($query, array $data) {
                                if ($data['start_date']) {
                                    $query->whereDate('start_date', '>=', $data['start_date']);
                                }
                            }),

                        Tables\Filters\Filter::make('end_date')
                            ->label('End Date Before')
                            ->form([
                                Forms\Components\DatePicker::make('end_date'),
                            ])
                            ->query(function ($query, array $data) {
                                if ($data['end_date']) {
                                    $query->whereDate('end_date', '<=', $data['end_date']);
                                }
                            }),
            ])
            
            ->actions([
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContracts::route('/'),
            'create' => Pages\CreateContracts::route('/create'),
            'edit' => Pages\EditContracts::route('/{record}/edit'),
        ];
    }
}
