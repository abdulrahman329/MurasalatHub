<?php

namespace App\Filament\Resources;

use App\Filament\Resources\CorrespondenceResource\Pages;
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
use Filament\Tables\Columns\TextColumn;

class CorrespondenceResource extends Resource
{
    // Define the model associated with this resource (Correspondence model)
    protected static ?string $model = Correspondence::class;

    // Set the icon to display in the navigation sidebar for this resource
    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'المرسلات'; // Group for navigation in the admin panel
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

                // Numeric input for correspondence number (should be unique and between 1 and 999)
                Forms\Components\TextInput::make('number')
                    ->unique() // Ensure the correspondence number is unique
                    ->numeric() // Ensure the number is numeric
                    ->minValue(1) // Minimum value for the number is 1
                    ->maxValue(999) // Maximum value for the number is 999
                    ->maxLength(3) // Maximum length is 3 digits
                    ->required(), // The number is a required field

                // Select field for sender department
                Forms\Components\Select::make('sender_department_id')
                    ->label('Sender Department')
                    ->relationship('senderDepartment', 'name') // Relationship to the senderDepartment model (displays department names)
                    ->searchable() // Allow searching for departments
                    ->required(), // The sender department is a required field

                // Select field for receiver department
                Forms\Components\Select::make('receiver_department_id')
                    ->label('Receiver Department')
                    ->relationship('receiverDepartment', 'name') // Relationship to the receiverDepartment model (displays department names)
                    ->searchable() // Allow searching for departments
                    ->required(), // The receiver department is a required field

                // Select field for the user who created the correspondence
                Forms\Components\Select::make('created_by')
                    ->label('Created By User')
                    ->relationship('creator', 'name') // Relationship to the creator model (displays user names)
                    ->searchable() // Allow searching for users
                    ->required(), // The creator is a required field

                // Select field for status (Pending, Approved, Rejected)
                Forms\Components\Select::make('status')
                    ->options([ // Status options
                        'pending' => 'Pending',
                        'approved' => 'Approved',
                        'rejected' => 'Rejected',
                    ])
                    ->required(), // Status is a required field

                // Select field for type of correspondence (email, letter, or fax)
                Forms\Components\Select::make('type')
                    ->options([ // Options for correspondence types
                        'email' => 'Email',
                        'letter' => 'Letter',
                        'fax' => 'Fax',
                    ]),

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

                // Display the correspondence ID (primary key)
                TextColumn::make('id'),

                // Display the subject of the correspondence
                TextColumn::make('subject'),

                // Display any additional notes related to the correspondence
                TextColumn::make('notes'),

                // Display the sender's department name
                TextColumn::make('senderDepartment.name')
                    ->label('Sender Department'),

                // Display the receiver's department name
                TextColumn::make('receiverDepartment.name')
                    ->label('Receiver Department'),

                // Display the type of correspondence (email, letter, or fax)
                TextColumn::make('type'),

                // Display the correspondence number, ensuring it is always 3 digits (e.g., 001, 002, etc.)
                TextColumn::make('number')
                    ->label('Number')
                    ->formatStateUsing(function ($state, $record) {
                        return str_pad($state, 3, '0', STR_PAD_LEFT); // Pad the number with leading zeros to make it 3 digits
                    }),

                // Display the file status (whether the file is available or not)
                TextColumn::make('file')
                    ->label('File')
                    ->formatStateUsing(function ($state) {
                        // If no file is uploaded, display "No File", otherwise display "File Available"
                        if ($state === null) {
                            return '❌ No File';
                        } else if (isset($state)) {
                            return '✅ File';
                        }
                    }),

                // Display the status of the correspondence (Pending, Approved, Rejected)
                TextColumn::make('status'),

                // Display the name of the user who created the correspondence
                TextColumn::make('creator.name')
                    ->label('Created By'),
            ])
            ->filters([ // Define filters to filter the data in the table

                // Search filter to filter by subject, ID, number, sender, or receiver department name
                Tables\Filters\Filter::make('search')  
                    ->form([
                        Forms\Components\TextInput::make('search')
                            ->label('Search for subject or id or number or senderDepartment Name or receiverDepartment name')
                            ->placeholder('subject or id or number or senderDepartment Name or receiverDepartment name'),
                    ])
                    ->query(function (Builder $query, array $data) {
                        if ($search = $data['search'] ?? null) {
                            $query->where(function ($q) use ($search) {
                                $q->where('id', 'like', "%{$search}%")
                                    ->orWhere('subject', 'like', "%{$search}%")
                                    ->orWhere('number', 'like', "%{$search}%")
                                    ->orWhereHas('senderDepartment', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%");
                                    })
                                    ->orWhereHas('receiverDepartment', function ($q) use ($search) {
                                        $q->where('name', 'like', "%{$search}%");
                                    });
                            });
                        }
                    }),

                // Filter by correspondence type (email, letter, fax)
                Tables\Filters\SelectFilter::make('type')
                    ->options([ // Options for the filter
                        'email' => 'Email',
                        'letter' => 'Letter',
                        'fax' => 'Fax',
                    ]),

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

                // Action to edit a correspondence record
                Tables\Actions\EditAction::make(),

                // Action to delete a correspondence record
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([ // Define bulk actions for multiple records

                // Bulk delete action for selected records
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),

                    // Bulk action to mark selected correspondences as Pending
                    Tables\Actions\BulkAction::make('markAsPending')
                        ->label('Mark as Pending')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->toArray(); // Get the IDs of the selected records
                            Correspondence::whereIn('id', $ids)->update(['status' => 'pending']); // Update the status to "pending"
                        })
                        ->requiresConfirmation() // Require confirmation before executing the action
                        ->color('warning'), // Set the color to warning

                    // Bulk action to mark selected correspondences as Approved
                    Tables\Actions\BulkAction::make('markAsApproved')
                        ->label('Mark as Approved')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->toArray(); // Get the IDs of the selected records
                            Correspondence::whereIn('id', $ids)->update(['status' => 'approved']); // Update the status to "approved"
                        })
                        ->requiresConfirmation() // Require confirmation before executing the action
                        ->color('success'), // Set the color to success

                    // Bulk action to mark selected correspondences as Rejected
                    Tables\Actions\BulkAction::make('markAsRejected')
                        ->label('Mark as Rejected')
                        ->action(function ($records) {
                            $ids = $records->pluck('id')->toArray(); // Get the IDs of the selected records
                            Correspondence::whereIn('id', $ids)->update(['status' => 'rejected']); // Update the status to "rejected"
                        })
                        ->requiresConfirmation() // Require confirmation before executing the action
                        ->color('danger'), // Set the color to danger
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
            'index' => Pages\ListCorrespondences::route('/'),

            // Route to the create page
            'create' => Pages\CreateCorrespondence::route('/create'),

            // Route to the edit page
            'edit' => Pages\EditCorrespondence::route('/{record}/edit'),
        ];
    }
}
