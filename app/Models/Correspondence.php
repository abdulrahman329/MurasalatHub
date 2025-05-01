<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Correspondence extends Model
{
/**
 * The table associated with the model.
 *
 * @var string
 */
protected $table = 'Correspondence';

/**
 * The attributes that are mass assignable.
 * These fields can be filled via mass assignment.
 *
 * @var array<string>
 */
protected $fillable = [
    'subject', // The subject of the correspondence
    'type', // The type of correspondence (e.g., email, letter)
    'number', // A unique number identifying the correspondence
    'sender_department_id', // Foreign key linking to the sender's department
    'receiver_department_id', // Foreign key linking to the receiver's department
    'file', // File attachment associated with the correspondence
    'notes', // Additional notes about the correspondence
    'status', // The status of the correspondence (e.g., pending, completed)
    'created_by', // Foreign key linking to the user who created the correspondence
];

/**
 * Define the relationship for the sender department.
 * A correspondence belongs to one sender department.
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function senderDepartment()
{
    return $this->belongsTo(Department::class, 'sender_department_id');
}

/**
 * Define the relationship for the receiver department.
 * A correspondence belongs to one receiver department.
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function receiverDepartment()
{
    return $this->belongsTo(Department::class, 'receiver_department_id');
}

/**
 * Define the relationship for the creator of the correspondence.
 * A correspondence belongs to one user who created it.
 *
 * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
 */
public function creator()
{
    return $this->belongsTo(User::class, 'created_by');
}

/**
 * Define the relationship for correspondence logs.
 * A correspondence can have many logs associated with it.
 *
 * @return \Illuminate\Database\Eloquent\Relations\HasMany
 */
public function logs()
{
    return $this->hasMany(CorrespondenceLog::class);
}
}