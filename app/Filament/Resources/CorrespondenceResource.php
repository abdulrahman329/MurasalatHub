<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorrespondenceResource\Pages;
use App\Models\Correspondence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Illuminate\Database\Eloquent\Builder;

class CorrespondenceResource extends Resource
{
    protected static ?string $model = Correspondence::class;

    protected static ?string $navigationIcon = 'heroicon-o-inbox';
    protected static ?string $navigationLabel = 'المراسلات';
    protected static ?string $modelLabel = 'مراسلة';
    protected static ?string $pluralModelLabel = 'المراسلات';
     protected static ?string $navigationGroup = 'المراسلات'; 
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('subject')
                    ->label('الموضوع')
                    ->required(),

                TextInput::make('type')
                    ->label('نوع المراسلة')
                    ->required(),

                TextInput::make('number')
                    ->label('رقم المراسلة')
                    ->required(),

                Select::make('sender_department_id')
                    ->label('القسم المرسل')
                    ->relationship('senderDepartment', 'name')
                    ->searchable()
                    ->required(),

                Select::make('receiver_department_id')
                    ->label('القسم المستقبل')
                    ->relationship('receiverDepartment', 'name')
                    ->searchable()
                    ->required(),

                TextInput::make('file')
                    ->label('الملف'),

                TextInput::make('notes')
                    ->label('ملاحظات'),

                Select::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending' => 'قيد الانتظار',
                        'completed' => 'مكتمل',
                    ])
                    ->required(),

                Select::make('created_by')
                    ->label('أنشئ بواسطة')
                    ->relationship('creator', 'name')
                    ->searchable()
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')->label('الموضوع'),
                TextColumn::make('type')->label('نوع المراسلة'),
                TextColumn::make('number')->label('رقم المراسلة'),
                TextColumn::make('senderDepartment.name')->label('القسم المرسل'),
                TextColumn::make('receiverDepartment.name')->label('القسم المستقبل'),
                TextColumn::make('status')->label('الحالة'),
                TextColumn::make('creator.name')->label('أنشئ بواسطة'),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCorrespondences::route('/'),
            'create' => Pages\CreateCorrespondence::route('/create'),
            'edit' => Pages\EditCorrespondence::route('/{record}/edit'),
        ];
    }
}
