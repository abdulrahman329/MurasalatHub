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
                TextInput::make('subject')
                    ->label('الموضوع')
                    ->required(),

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
                    ]) // Ensure all labels are valid strings
                    ->required(),

                    // Hidden field for sender_department_id, auto-filled from the authenticated user (with fallback)
                    Forms\Components\Hidden::make('sender_department_id')
                        ->default(fn () => auth()->user()?->department_id)
                        ->required(),

                Select::make('receiver_department_id')
                    ->label('القسم المستقبل')
                    ->relationship('receiverDepartment', 'name')
                    ->searchable()
                    ->required(),

                Textarea::make('notes')
                    ->label('ملاحظات'),

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

                // Hidden field for status, auto-filled to 'pending'
                Forms\Components\Hidden::make('status')
                ->default('قيد الانتظار'),

                    // Hidden field for user_id, auto-filled from the authenticated user
                    Forms\Components\Hidden::make('created_by')
                    ->default(fn () => auth()->id())
                    ->required(),

            ]);
    }
  
    public static function table(Table $table): Table
    {
        return $table
        ->modifyQueryUsing(function (\Illuminate\Database\Eloquent\Builder $query) {
            $query->where('receiver_department_id', auth()->user()->department_id);
        })
            ->columns([
                TextColumn::make('subject')
                ->label('الموضوع')
                ->searchable(),  // Make the select input searchable

                TextColumn::make('creator.name')
                ->label('أنشئ بواسطة')
                ->searchable()  // Make the select input searchable
                ->sortable(), // Allow sorting by creator name

                TextColumn::make('senderDepartment.name')
                ->label('القسم المرسل')
                ->searchable()  // Make the select input searchable
                ->sortable(), // Allow sorting by sender department name

                TextColumn::make('receiverDepartment.name')
                ->label('القسم المستقبل')
                ->searchable()  // Make the select input searchable
                ->sortable(), // Allow sorting by Receiver department name

                TextColumn::make('type')
                ->label('نوع المراسلة')
                ->searchable()  // Make the column searchable
                ->sortable(), // Allow sorting by type

                TextColumn::make('notes')
                    ->label('ملاحظات'),

                TextColumn::make('status')
                ->label('الحالة')
                ->searchable()  // Make the select input searchable
                ->sortable() // Allow sorting by status
                ->getStateUsing(function ($record) {
                    // Example: Show an icon or text based on status
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

            ])

            ->filters([

            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    // Custom action to view details of a correspondence record
                    // Tables\Actions\Action::make('view')
                    //     ->label('View')
                    //     ->url(fn ($record) => route('filament.admin.resources.correspondences.view', $record->id))
                    //     ->icon('heroicon-o-eye'),

                    // Action to edit a correspondence record
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

                    // Action to delete a correspondence record
                    Tables\Actions\DeleteAction::make()->label('حذف'),

                   // Action to mark as Approved
                    Tables\Actions\Action::make('markAsApproved')
                        ->label('وضع علامة على الموافقة')
                        ->action(function ($record) {
                            $userName = auth()->user()->name ?? 'غير معروف';
                            $departmentName = $record->receiverDepartment->name ?? 'غير معروف';

                                $record->update([
                                    'status' => 'الموافقة',
                                    'notes' => "تمت الموافقة على المراسلة من قبل قسم {$departmentName} بواسطة المستخدم {$userName}",
                                ]);

                            Correspondence_log::create([
                                'correspondence_id' => $record->id,
                                'user_id' => $record->created_by,
                                'action' => 'الموافقة',
                                'note' => "تمت الموافقة على المراسلة من قبل قسم {$departmentName} بواسطة المستخدم {$userName}",
                            ]);
                        })
                        ->requiresConfirmation()
                        ->color('success'),


                    // Action to mark as Rejected
                    Tables\Actions\Action::make('markAsRejected')
                        ->label('وضع علامة على الرفض')
                        ->action(function ($record) {
                            $userName = auth()->user()->name ?? 'غير معروف';
                            $departmentName = $record->receiverDepartment->name ?? 'غير معروف';

                                $record->update([
                                    'status' => 'مرفوض',
                                    'notes' => "تم رفض المراسلة من قبل قسم {$departmentName} بواسطة المستخدم {$userName}",
                        ]);

                        Correspondence_log::create([
                            'correspondence_id' => $record->id,
                            'user_id' => $record->created_by,
                            'action' => 'مرفوض',
                            'note' => "تم رفض المراسلة من قبل قسم {$departmentName} بواسطة المستخدم {$userName}",
                        ]);
                    })
                    ->requiresConfirmation()
                    ->color('danger'),


                    // // Action to mark a single correspondence as Approved
                    // Tables\Actions\Action::make('markAsApproved')
                    //     ->label('وضع علامة على الموافقة')
                    //     ->action(function ($record) {
                    //         $record->update([
                    //             'status' => 'الموافقة',
                    //             'notes' => 'تمت الموافقة على المراسلة من قبل قسم ' . ($record->receiverDepartment->name ?? 'غير معروف'),

                    //     ]);
                            
                    //         // Find the existing CorrespondenceLog and update it
                    //         $log = Correspondence_log::where('correspondence_id', $record->id)->first();
                    //         if ($log) {
                    //             $log->update([
                    //                 'action' => 'الموافقة',
                    //                 'note' => 'تمت الموافقة على المراسلة من قبل قسم ' . ($record->receiverDepartment->name ?? 'غير معروف'),
                    //             ]);
                    //         }
                    //     })
                    //     ->requiresConfirmation()
                    //     ->color('success'),

                    // // Action to mark a single correspondence as Rejected
                    // Tables\Actions\Action::make('markAsRejected')
                    //     ->label('وضع علامة على الرفض')
                    //     ->action(function ($record) {
                    //         $record->update([
                    //             'status' => 'مرفوض',
                    //             'notes' => 'تم رفض المراسلة من قبل قسم ' . ($record->receiverDepartment->name ?? 'غير معروف'),
                    //         ]);

                            
                    //         // Find the existing CorrespondenceLog and update it
                    //         $log = Correspondence_log::where('correspondence_id', $record->id)->first();
                    //         if ($log) {
                    //             $log->update([
                    //                 'action' => 'مرفوض',
                    //                 'note' => 'تم رفض المراسلة من قبل قسم ' . ($record->receiverDepartment->name ?? 'غير معروف'),
                    //             ]);
                    //         }
                    //     })
                    //     ->requiresConfirmation()
                    //     ->color('danger'),

                        Tables\Actions\Action::make('forwardToDepartment')
                            ->label('الموافقة وتحويل إلى قسم آخر')
                            ->form([
                                Forms\Components\Select::make('receiver_department_id')
                                    ->label('اختر القسم الجديد')
                                    ->relationship('receiverDepartment', 'name')
                                    ->required()
                                    ->options(function ($record) {
                                        // Exclude current sender and receiver departments
                                        $departments = \App\Models\Department::query();
                                        if ($record) {
                                            $departments->where('id', '!=', $record->sender_department_id)
                                                ->where('id', '!=', $record->receiver_department_id);
                                        }
                                        return $departments->pluck('name', 'id');
                                    }),
                            ])
                            ->action(function ($record, array $data) {
                                // Update the correspondence with the new department

                                $record->update([
                                    'receiver_department_id' => $data['receiver_department_id'],
                                    'status' => '⏳ قيد الانتظار',
                                    'notes' => 'تمت الموافقة على المراسلة من قبل قسم ' . ($record->senderDepartment->name ?? 'غير معروف') . ' إلى قسم ' . ($record->receiverDepartment->name ?? 'غير معروف'),
                                ]);

                                // Log the forwarding action
                                Correspondence_Log::create([
                                    'correspondence_id' => $record->id,
                                    'user_id' => $record->created_by,
                                    'action' => 'الموافقة وتحويل إلى قسم آخر',
                                    'note' => 'تمت الموافقة على المراسلة من قبل قسم ' . ($record->senderDepartment->name ?? 'غير معروف') . ' إلى قسم ' . ($record->receiverDepartment->name ?? 'غير معروف'),
                                ]);
                            })
                            ->icon('heroicon-o-arrow-right')
                            ->color('success')
                            ->requiresConfirmation(),
            ])
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