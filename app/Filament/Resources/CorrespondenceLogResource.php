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


                // Automatically set the user_id to the currently authenticated user and hide the field
                Forms\Components\TextInput::make('user_id')
                    ->default(fn () => auth()->id())
                    ->searchable()
                    ->required(),

                    
                // Automatically set the correspondence_id if provided in the request, otherwise leave empty
                Forms\Components\TextInput::make('correspondence_id')
                ->searchable()
                // ->label('Correspondence ID')
                //     ->options(
                //         Correspondence::all()->pluck('id')
                //     )
                ->required(), // Make this field required

                //->label('أنشأ بواسطة')
                //->label('رقم المراسلة')

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
                    ->label('رقم المراسلة'),

                TextColumn::make('user.name')
                    ->label('أنشأ بواسطة'),

                TextColumn::make('note')
                    ->label('الملاحظة'),

                TextColumn::make('action')
                ->label('الحالة')
                ->getStateUsing(function ($record) {
                    // Example: Show an icon or text based on status
                    switch ($record->action) {
                        case 'الموافقة':
                            return '✅ الموافقة';
                        case 'قيد الانتظار':
                            return '⏳ قيد الانتظار';
                        case 'مرفوض':
                            return '❌ مرفوض';
                        case 'الموافقة وتحويل إلى قسم آخر';
                            return '🔄 الموافقة وتحويل إلى قسم آخر';
                        default:
                            return 'قيد الانتظار';
                    }
                }),
                TextColumn::make('created_at')
                    ->label('تم إنشاؤه في')
                    ->sortable()
                    ->dateTime('d/m/Y h:i A '),


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
                    Tables\Filters\SelectFilter::make('action')
                        ->label('الإجراء')
                        ->options([
                            'الموافقة' => 'الموافقة',
                            'قيد الانتظار' => 'قيد الانتظار',
                            'مرفوض' => 'مرفوض',
                            'الموافقة وتحويل إلى قسم آخر' => 'الموافقة وتحويل إلى قسم آخر',
                        ])
                        ->searchable(),
                Tables\Filters\SelectFilter::make('user_id')
                    ->label('أنشأ بواسطة')
                    ->relationship('user', 'name')
                    ->searchable(),
            ])
            ->actions([
                // Tables\Actions\ViewAction::make()
                //     ->icon('heroicon-o-eye')
                //     ->url(fn ($record) => route('filament.admin.resources.correspondence-logs.view', $record->id))
                //     ->openUrlInNewTab(),
                // Tables\Actions\EditAction::make()->label('تعديل'),
                // Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canCreate(): bool
    {
        return false;  // Disables the "New" button
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListCorrespondenceLogs::route('/'),
            // 'create' => Pages\CreateCorrespondenceLog::route('/create'),
            // 'edit' => Pages\EditCorrespondenceLog::route('/{record}/edit'),
        ];
    }
}