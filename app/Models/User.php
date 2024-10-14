<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;
use Tymon\JWTAuth\Contracts\JWTSubject; // Import the JWTSubject interface

class User extends Authenticatable implements JWTSubject // Implement the interface for JWT authentication
{
    use HasFactory, Notifiable, HasRoles; // Traits used for factory, notifications, and role management

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',     // User's full name
        'email',    // User's email address
        'password', // User's password (hashed in the database)
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [
        'password',        // Hide the password from serialized output
        'remember_token',  // Hide the remember token from serialized output
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime', // Cast to datetime for email verification timestamp
            'password' => 'hashed',             // Automatically hash the password
        ];
    }

    /**
     * Define a one-to-many relationship with the Log model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        return $this->hasMany(Log::class); // Each user can have many logs
    }

    /**
     * Get the identifier that will be stored in the JWT.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey(); // Return the unique key of the user (usually the ID)
    }

    /**
     * Return custom claims for the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return []; // You can return custom claims here if needed for your application
    }
}
