<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorrespondenceResource\Pages;
use App\Filament\Resources\CorrespondenceResource\Pages\ListCorrespondence;
use App\Models\Correspondence;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Filters;
use Filament\Tables\Filters\DateFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Hidden;
use Filament\Tables\Columns\TextColumn;
use App\Models\Correspondence_log;

class CorrespondenceResource extends Resource
{
    // Define the model associated with this resource (Correspondence model)
    protected static ?string $model = Correspondence::class;

    // Set the icon to display in the navigation sidebar for this resource
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    /**
     * Define the form used to create or edit a correspondence entry.
     * 
     * @param Form $form
     * @return Form
     */
    public static function form(Form $form): Form
    {
        return $form
            ->schema([ // Define the fields in the form

                // Text input for subject (required field)
                Forms\Components\TextInput::make('subject')
                    ->required(), // The subject is a required field

                    // Hidden field for user_id, auto-filled from the authenticated user
                    Forms\Components\Hidden::make('created_by')
                    ->default(fn () => auth()->id())
                        ->required(),

                    // Hidden field for sender_department_id, auto-filled from the authenticated user (with fallback)
                    Forms\Components\Hidden::make('sender_department_id')
                        ->default(fn () => auth()->user()?->department_id)
                        ->required(),

                // Select field for receiver department
                Forms\Components\Select::make('receiver_department_id')
                    ->label('Receiver Department')
                    ->relationship('receiverDepartment', 'name') // Ensure 'name' is a valid string in the related model
                    ->searchable() // Allow searching for departments
                    ->required(), // The receiver department is a required field

                    // Hidden field for status, auto-filled to 'pending'
                    Forms\Components\Hidden::make('status')
                        ->default('pending'),
                    
                // Select field for type of correspondence 
                Forms\Components\Select::make('type')
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
                    ]), // Ensure all labels are valid strings

                    // Textarea for notes related to the correspondence
                Forms\Components\Textarea::make('notes'),

                // File upload field for correspondence files
                Forms\Components\FileUpload::make('file'),
            ]);
    }

    /**
     * Define the table displaying the correspondence entries.
     * 
     * @param Table $table
     * @return Table
     */
    public static function table(Table $table): Table
    {
        return $table
            ->columns([ // Define the columns displayed in the table

                // Display the subject of the correspondence
                TextColumn::make('subject')
                ->searchable(),  // Make the select input searchable


                // Display any additional notes related to the correspondence
                TextColumn::make('notes'),
                
                // Display the sender's department name
                TextColumn::make('senderDepartment.name')
                    ->label('Sender Department')
                    ->searchable()  // Make the select input searchable
                    ->sortable(), // Allow sorting by sender department name

                // Display the receiver's department name
                TextColumn::make('receiverDepartment.name')
                    ->label('Receiver Department')
                    ->searchable()  // Make the select input searchable
                    ->sortable(), // Allow sorting by Receiver department name

                // Display the type of correspondence (email, letter, or fax)
                TextColumn::make('type')
                    ->label('Type')
                    ->searchable()  // Make the select input searchable
                    ->sortable(), // Allow sorting by Type

                    TextColumn::make('file')  // File column
                    ->label('File')
                    ->getStateUsing(function ($record) {
                    // Add custom logic for determining if a file exists
                    if (empty($record->file)) {
                        return '❌ No File';
                    }
    
                        // Return custom file info, e.g., filename or extension
                        return '✅ ' . pathinfo($record->file, PATHINFO_EXTENSION) . ' File';
                    }),

                // Display the status of the correspondence (Pending, Approved, Rejected)
                TextColumn::make('status')
                    ->label('Status')
                    ->searchable()  // Make the select input searchable
                    ->sortable() // Allow sorting by status
                    ->getStateUsing(function ($record) {
                        // Example: Show an icon or text based on status
                        switch ($record->status) {
                            case 'approved':
                            return '✅ Approved';
                             case 'pending':
                            return '⏳ Pending';
                             case 'rejected':
                            return '❌ Rejected';
                            default:
                            return 'Pending';
                    }
                }),

                // Display the name of the user who created the correspondence
                TextColumn::make('creator.name')
                    ->label('Created By')
                    ->searchable()  // Make the select input searchable
                    ->sortable(), // Allow sorting by creator name
                    
            ])
            ->filters([ // Define filters to filter the data in the table

                // Search filter to filter by subject, ID, sender, or receiver department name
                Tables\Filters\Filter::make('search')  
                    ->form([
                        Forms\Components\TextInput::make('search')
                            ->label('Search for subject or id or senderDepartment Name or receiverDepartment name')
                            ->placeholder('subject or id or senderDepartment Name or receiverDepartment name'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($search = $data['search'] ?? null) {
                            $query->where(function ($q) use ($search) {
                                $q->where('id', 'like', "%{$search}%")
                                    ->orWhere('subject', 'like', "%{$search}%")
                                    ->orWhereHas('senderDepartment', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('receiverDepartment', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%");
                                    });
                            });
                        }
                    }),
                    

                // Filter by correspondence status (Pending, Approved, Rejected)
                Tables\Filters\SelectFilter::make('status')
                    ->options([ // Options for the filter
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ]),

                // Filter by the user who created the correspondence
                Tables\Filters\SelectFilter::make('created_by')
                    ->label('Created By User')
                    ->relationship('creator', 'name') // Relationship to the creator model
                    ->searchable(), // Allow searching for users
            ])
            ->actions([ // Define actions for each record in the table
                Tables\Actions\ActionGroup::make([
                    // Custom action to view details of a correspondence record
                    Tables\Actions\Action::make('view')
                        ->label('View')
                        ->url(fn ($record) => route('filament.admin.resources.correspondences.view', $record->id))
                        ->icon('heroicon-o-eye'),

                    // Action to edit a correspondence record
                    Tables\Actions\EditAction::make(),

                    // Action to delete a correspondence record
                    Tables\Actions\DeleteAction::make(),
                    // Action to mark a single correspondence as Approved
                    Tables\Actions\Action::make('markAsApproved')
                        ->label('Mark as Approved')
                        ->action(function ($record) {
                            $record->update(['status' => 'approved']);
                            
                            // Find the existing CorrespondenceLog and update it
                            $log = Correspondence_log::where('correspondence_id', $record->id)->first();
                            if ($log) {
                                $log->update([
                                    'action' => 'approved',
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->color('success'),
                    // Action to mark a single correspondence as Rejected
                    Tables\Actions\Action::make('markAsRejected')
                        ->label('Mark as Rejected')
                        ->action(function ($record) {
                            $record->update(['status' => 'rejected']);
                            
                            // Find the existing CorrespondenceLog and update it
                            $log = Correspondence_log::where('correspondence_id', $record->id)->first();
                            if ($log) {
                                $log->update([
                                    'action' => 'rejected',
                                ]);
                            }
                        })
                        ->requiresConfirmation()
                        ->color('danger'),
                ]),
            ])
            ->bulkActions([ // Define bulk actions for multiple records
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    /**
     * Define any relations to other resources (currently not used).
     * 
     * @return array
     */
    public static function getRelations(): array
    {
        return [
            // Define relationships to other resources if needed
        ];
    }

    /**
     * Define the pages for listing, creating, and editing correspondences.
     * 
     * @return array
     */
    public static function getPages(): array
    {
        return [
            // Route to the list page
            'index' => Pages\ListCorrespondence::route('/'),
            'edit' => Pages\EditCorrespondence::route('/{record}/edit'), // Route to edit a correspondence
            'create' => Pages\CreateCorrespondence::route('/create'), // Route to create a new correspondence
            
            // Route to view a specific correspondence 
            'view' => Pages\ViewCorrespondence::route('/{record}/view'),
        ];
    }

}
