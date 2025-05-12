<?php

namespace App\Filament\Resources;

use Filament\Forms\Components\{TextInput, DatePicker, FileUpload, Select};
use Filament\Tables\Columns\{TextColumn};
use Filament\Tables\Actions\{EditAction, DeleteBulkAction, DeleteAction, BulkActionGroup};
use App\Filament\Resources\ContractsResource\Pages\{ListContracts, CreateContracts, EditContracts};
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

    protected static ?string $navigationGroup = 'العقود';

    public static function form(Form $form): Form
    {
        return $form->schema([
            TextInput::make('title')
                ->label('عنوان العقد')
                ->required(),

            TextInput::make('contract_type')
                ->label('نوع العقد')
                ->required(),

            TextInput::make('party_name')
                ->label('اسم الطرف المتعاقد')
                ->required(),

            Select::make('responsible_user_id')
                ->label('المسؤول')
                ->relationship('responsibleUser', 'name')
                ->searchable()
                ->required(),

            DatePicker::make('start_date')
                ->label('تاريخ البدء')
                ->required()
                ->before('end_date'),

            DatePicker::make('end_date')
                ->label('تاريخ الانتهاء')
                ->required()
                ->after('start_date'),

            FileUpload::make('file')
                ->label('الملف')
                ->directory('contracts')
                ->acceptedFileTypes(['application/pdf', 'image/*'])
                ->maxSize(10240),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('عنوان العقد')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('responsibleUser.name')
                    ->label('المسؤول'),

                TextColumn::make('contract_type')
                    ->label('نوع العقد'),

                TextColumn::make('party_name')
                    ->label('اسم الطرف'),

                TextColumn::make('file')
                    ->label('الملف')
                    ->formatStateUsing(fn ($state) => $state ? '✅ يوجد ملف' : '❌ لا يوجد ملف'),

                TextColumn::make('start_date')
                    ->label('تاريخ البدء')
                    ->sortable()
                    ->date(),

                TextColumn::make('end_date')
                    ->label('تاريخ الانتهاء')
                    ->sortable()
                    ->date(),
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
            ->actions([
                EditAction::make()->label('تعديل'),
                DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()->label('حذف جماعي'),
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
            'index' => ListContracts::route('/'),
            'create' => CreateContracts::route('/create'),
            'edit' => EditContracts::route('/{record}/edit'),
        ];
    }
}
