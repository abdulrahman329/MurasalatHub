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

    protected static ?string $navigationGroup = 'المراسلات';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                BelongsToSelect::make('user_id')
                    ->relationship('user', 'name')
                    ->label('أنشأ بواسطة')
                    ->searchable()
                    ->required(),

                Select::make('correspondence_id')
                    ->label('رقم المراسلة')
                    ->options(
                        Correspondence::all()->pluck('number', 'id')
                    )
                    ->searchable()
                    ->required(),

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
                    ->label('الملاحظة')
                    ->limit(50),

                TextColumn::make('created_at')
                    ->label('تاريخ الإنشاء')
                    ->sortable(),
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
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
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
            'index' => Pages\ListCorrespondenceLogs::route('/'),
            'create' => Pages\CreateCorrespondenceLog::route('/create'),
            'edit' => Pages\EditCorrespondenceLog::route('/{record}/edit'),
        ];
    }
}
