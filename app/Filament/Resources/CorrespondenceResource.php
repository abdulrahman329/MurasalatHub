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
use Filament\Forms\Components\Hidden;
use App\Models\Correspondence_log;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;

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
                // Input for the subject of the correspondence
                TextInput::make('subject')
                    ->label('الموضوع')
                    ->required(),

                // Dropdown for selecting the type of correspondence
                Select::make('type')
                    ->label('نوع المراسلة')
                    ->options([
                        'email' => 'Email',
                        'letter' => 'Letter',
                        'fax' => 'Fax',
                        'memo' => 'Memo',
                        'report' => 'Report',
                        'notification' => 'Notification',
                        'circular' => 'Circular',
                        'invoice' => 'Invoice',
                        'other' => 'Other',
                    ])
                    ->required(),

                // Hidden field for the sender's department ID, auto-filled from the authenticated user
                Hidden::make('sender_department_id')
                    ->default(fn () => auth()->user()?->department_id)
                    ->required(),

                // Dropdown for selecting the receiver's department
                Select::make('receiver_department_id')
                    ->label('القسم المستقبل')
                    ->relationship(
                        name: 'receiverDepartment',
                        titleAttribute: 'name',
                        modifyQueryUsing: fn ($query) => $query->where('id', '!=', auth()->user()?->department_id)->select(['id', 'name']) // Exclude sender's department
                    )
                    ->searchable()
                    ->required(),

                // Textarea for additional notes
                Textarea::make('notes')
                    ->label('ملاحظات'),

                // File upload for attaching files
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

                // Hidden field for the status, defaulted to 'pending'
                Forms\Components\Hidden::make('status')
                    ->default('قيد الانتظار'),

                // Hidden field for the user ID, auto-filled from the authenticated user
                Forms\Components\Hidden::make('created_by')
                    ->default(fn () => auth()->id())
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query) {
                // Show records where the user is either the sender or the receiver
                $query->where('sender_department_id', auth()->user()->department_id)
                      ->orWhere('receiver_department_id', auth()->user()->department_id);
            })
            ->columns([
                // Column for the subject
                TextColumn::make('subject')
                    ->label('الموضوع')
                    ->searchable(),

                // Column for the creator's name
                TextColumn::make('creator.name')
                    ->label('أنشئ بواسطة')
                    ->searchable()
                    ->sortable(),

                // Column for the sender's department
                TextColumn::make('senderDepartment.name')
                    ->label('القسم المرسل')
                    ->searchable()
                    ->sortable(),

                // Column for the receiver's department
                TextColumn::make('receiverDepartment.name')
                    ->label('القسم المستقبل')
                    ->searchable()
                    ->sortable(),

                // Column for the type of correspondence
                TextColumn::make('type')
                    ->label('نوع المراسلة')
                    ->searchable()
                    ->sortable(),

                // Column for notes
                TextColumn::make('notes')
                    ->label('ملاحظات')
                    ->wrap(),

                // Column for the status with icons
                TextColumn::make('status')
                    ->label('الحالة')
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(function ($record) {
                        switch ($record->status) {
                            case 'الموافقة':
                                return '✅ الموافقة';
                            case 'قيد الانتظار':
                                return '⏳ قيد الانتظار';
                            case 'مرفوض':
                                return '❌ مرفوض';
                            default:
                                return 'قيد الانتظار';
                        }
                    }),

                // Column for the file with custom logic
                TextColumn::make('file')
                    ->label('ملف')
                    ->getStateUsing(function ($record) {
                        if (empty($record->file)) {
                            return '❌ لا يوجد ملف';
                        }
                        return '✅ ' . pathinfo($record->file, PATHINFO_EXTENSION) . ' ملف';
                    }),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Edit action, visible only to the sender or creator
                    Tables\Actions\EditAction::make()
                        ->label('تعديل')
                        ->color('warning')
                        ->visible(function ($record) {
                            $user = auth()->user();
                            return $user && (
                                $record->sender_department_id == $user->department_id ||
                                $record->created_by == $user->id
                            );
                        }),

                    // Delete action
                    Tables\Actions\DeleteAction::make()->label('حذف'),

                    // Mark as approved action, visible only to the receiver department
                    Tables\Actions\Action::make('markAsApproved')
                        ->label('وضع علامة على الموافقة')
                        ->action(function ($record) {
                            $userName = auth()->user()->name ?? 'غير معروف';
                            $departmentName = $record->receiverDepartment->name ?? 'غير معروف';

                            $record->update([
                                'status' => 'الموافقة',
                                'notes' => "تمت الموافقة ✅ على المراسلة من قبل قسم 🔸{$departmentName}🔹 بواسطة المستخدم 👤{$userName}👤",
                            ]);

                            Correspondence_log::create([
                                'correspondence_id' => $record->id,
                                'user_id' => $record->created_by,
                                'action' => 'الموافقة',
                                'note' => "تمت الموافقة ✅ على المراسلة من قبل قسم 🔸{$departmentName}🔹 بواسطة المستخدم 👤{$userName}👤",
                            ]);
                        })
                        ->requiresConfirmation()
                        ->color('success')
                        ->visible(function ($record) {
                            return auth()->user()?->department_id === $record->receiver_department_id;
                        }),

                    // Mark as rejected action, visible only to the receiver department
                    Tables\Actions\Action::make('markAsRejected')
                        ->label('وضع علامة على الرفض')
                        ->action(function ($record) {
                            $userName = auth()->user()->name ?? 'غير معروف';
                            $departmentName = $record->receiverDepartment->name ?? 'غير معروف';

                            $record->update([
                                'status' => 'مرفوض',
                                'notes' => "تم رفض ❌ المراسلة من قبل قسم 🔹{$departmentName}🔸 بواسطة المستخدم 👤{$userName}👤",
                            ]);

                            Correspondence_log::create([
                                'correspondence_id' => $record->id,
                                'user_id' => $record->created_by,
                                'action' => 'مرفوض',
                                'note' => "تم رفض ❌ المراسلة من قبل قسم 🔹{$departmentName}🔸 بواسطة المستخدم 👤{$userName}👤",
                            ]);
                        })
                        ->requiresConfirmation()
                        ->color('danger')
                        ->visible(function ($record) {
                            return auth()->user()?->department_id === $record->receiver_department_id;
                        }),

                    // Forward to department action, visible only to the receiver department
                    Tables\Actions\Action::make('forwardToDepartment')
                        ->label('الموافقة وتحويل إلى قسم آخر')
                        ->form([
                            Forms\Components\Select::make('new_receiver_department_id')
                                ->label('اختر القسم الجديد')
                                ->options(function ($record) {
                                    return \App\Models\Department::query()
                                        ->whereNotIn('id', [
                                            $record->sender_department_id,
                                            $record->receiver_department_id,
                                        ])
                                        ->pluck('name', 'id');
                                })
                                ->required(),
                        ])
                        ->action(function ($record, array $data) {
                            $oldDept = $record->receiverDepartment?->name ?? 'غير معروف';
                            $newDept = \App\Models\Department::find($data['new_receiver_department_id'])?->name ?? 'غير معروف';
                            $senderDept = $record->senderDepartment?->name ?? 'غير معروف';
                            $userName = auth()->user()->name;

                            // Update correspondence
                            $record->update([
                                'receiver_department_id' => $data['new_receiver_department_id'],
                                'status' => 'قيد الانتظار',
                                'notes' => "📝تمت الموافقة✅على المراسلة من قبل قسم🔹($senderDept)🔸 وتحويلها من قسم 🔸 ($oldDept) 🔹 إلى قسم 🏢 ($newDept) 🏢 بواسطة 👤 ($userName) 👤 ",
                            ]);

                            // Create log
                            Correspondence_log::create([
                                'correspondence_id' => $record->id,
                                'user_id' => $record->created_by,
                                'action' => 'الموافقة وتحويل إلى قسم آخر',
                                'note' => " 📝 تمت الموافقة ✅ على المراسلة من قبل قسم 🔹( $senderDept )🔸  وتحويلها من قسم 🔸($oldDept)🔹 إلى قسم 🏢 ( $newDept ) 🏢 بواسطة 👤 ( $userName ) 👤 ",
                            ]);
                        })
                        ->icon('heroicon-o-arrow-right')
                        ->color('success')
                        ->requiresConfirmation()
                        ->visible(function ($record) {
                            return auth()->user()?->department_id === $record->receiver_department_id;
                        }),
                ]),
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