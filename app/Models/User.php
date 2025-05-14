<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, HasProfilePhoto, Notifiable, TwoFactorAuthenticatable;


    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
    
    /**
     * The attributes that are mass assignable.
     * These are the fields that can be filled via mass assignment.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name', // User's name
        'email', // User's email address
        'password', // User's password
        'department_id', // Foreign key linking the user to a department
    ];

    /**
     * The attributes that should be hidden for serialization.
     * These fields will not be included in JSON responses.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password', // Hide the password field
        'remember_token', // Hide the remember token field
        'two_factor_recovery_codes', // Hide two-factor recovery codes
        'two_factor_secret', // Hide two-factor secret
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */
    protected $appends = [
        'profile_photo_url',
    ];

    /**
     * Get the attributes that should be cast.
     * This defines how certain fields should be automatically converted.
     *
     * @return array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime', // Cast email verification timestamp to a DateTime object
        'password' => 'hashed', // Automatically hash the password
    ];

    /**
     * Define the relationship between User and Department.
     * A user belongs to one department.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function department()
    {
        return $this->belongsTo(Department::class);
    }

    /**
     * Define the relationship between User and Contract.
     * A user can be responsible for many contracts.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function contracts()
    {
        return $this->hasMany(Contract::class, 'responsible_user_id');
    }

    /**
     * Define the relationship between User and Correspondence.
     * A user can create many correspondences.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function createdCorrespondences()
    {
        return $this->hasMany(Correspondence::class, 'created_by');
    }

    /**
     * Define the relationship between User and CorrespondenceLog.
     * A user can have many correspondence logs.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function correspondenceLogs()
    {
        return $this->hasMany(CorrespondenceLog::class); // Fixed class name casing
    }
}
