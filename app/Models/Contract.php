<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Contract extends Model
{
    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contracts'; // Fixed table name casing

    /**
     * The attributes that are mass assignable.
     * These fields can be filled via mass assignment.
     *
     * @var array<string>
     */
    protected $fillable = [
        'title', // The title of the contract
        'content', // The title of the contract
        'start_date', // The start date of the contract
        'end_date', // The end date of the contract
        'contract_type', // The type of the contract (e.g., service, employment)
        'party_name', // The name of the other party involved in the contract
        'file', // File attachment associated with the contract
        'responsible_user_id', // Foreign key linking to the user responsible for the contract
    ];

    public $timestamps = false;

    /**
     * Define the relationship for the responsible user.
     * A contract is managed by one responsible user.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function responsibleUser()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }
}
