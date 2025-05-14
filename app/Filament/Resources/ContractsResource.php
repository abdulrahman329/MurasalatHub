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
use Filament\Forms\Components\Hidden;


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

            Select::make('contract_type')
                ->label('نوع العقد')
                ->options([
                    'عقد شراء' => 'عقد شراء',
                    'عقد خدمة' => 'عقد خدمة',
                    'عقد استشاري' => 'عقد استشاري',
                    'عقد إيجار' => 'عقد إيجار',
                    'عقد صيانة' => 'عقد صيانة',
                    'عقد بيع' => 'عقد بيع',
                    'عقد عمل' => 'عقد عمل',
                ])
                ->required(),

                // Hidden field for user_id, auto-filled from the authenticated user
                Hidden::make('responsible_user_id')
                ->default(fn () => auth()->id())
                ->required(),

            DatePicker::make('start_date')
                ->label('تاريخ البدء')
                ->required()
                ->before('end_date')  // Ensure that start date is before the end date
                ->minDate(\Illuminate\Support\Carbon::today()),  // Ensure start date is not before today

            DatePicker::make('end_date')
                ->label('تاريخ الانتهاء')
                ->required()
                ->after('start_date'),

            // Accept more than just PDF and image files (e.g., Word, Excel, PowerPoint, ZIP)
            FileUpload::make('file')
                ->label('الملف')
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

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('responsibleUser.name')
                    ->label('المسؤول'),

                TextColumn::make('title')
                    ->label('عنوان العقد')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('contract_type')
                    ->label('نوع العقد'),

                    TextColumn::make('file')  // File column
                    ->label('ملف')
                    ->getStateUsing(function ($record) {
                    // Add custom logic for determining if a file exists
                    if (empty($record->file)) {
                        return '❌ لا يوجد ملف';
                    }
    
                        // Return custom file info, e.g., filename or extension
                        return '✅ ' . pathinfo($record->file, PATHINFO_EXTENSION) . ' ملف';
                    }),

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
                    DeleteBulkAction::make()->label('حذف المحدد'),
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
            // 'create' => CreateContracts::route('/create'),
            // 'edit' => EditContracts::route('/{record}/edit'),
        ];
    }
}
