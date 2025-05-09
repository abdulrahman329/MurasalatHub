<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Correspondence_log extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'correspondence_logs';

    /**
     * The attributes that are mass assignable.
     * These fields can be filled via mass assignment.
     *
     * @var array<string>
     */
    protected $fillable = [
        'correspondence_id', // Foreign key linking to the correspondence
        'user_id', // Foreign key linking to the user who performed the action
        'action', // The action performed (e.g., created, updated)
        'note', // Additional notes about the action
    ];

    /**
     * Indicates if the model should be timestamped.
     * This disables automatic created_at and updated_at fields.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be cast to dates.
     * This ensures proper handling of date fields.
     *
     * @var array<string>
     */
    protected $dates = [
        'created_at', // The timestamp when the log was created
    ];

    /**
     * Define the relationship between CorrespondenceLog and Correspondence.
     * A correspondence log belongs to one correspondence.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function correspondence()
    {
        return $this->belongsTo(Correspondence::class);
    }

    /**
     * Define the relationship between CorrespondenceLog and User.
     * A correspondence log belongs to one user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
