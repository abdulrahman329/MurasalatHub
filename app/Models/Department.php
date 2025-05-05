<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    protected $table = 'departments';

    protected $fillable = [
        'name',
    ];

    public $timestamps = false; // Disable automatic timestamps

    /**
     * Define the relationship between Department and User.
     * A department has many users.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function users()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Define the relationship for sent correspondences.
     * A department can send many correspondences.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function sentCorrespondences()
    {
        return $this->hasMany(Correspondence::class, 'sender_department_id');
    }

    /**
     * Define the relationship for received correspondences.
     * A department can receive many correspondences.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function receivedCorrespondences()
    {
        return $this->hasMany(Correspondence::class, 'receiver_department_id');
    }
}
