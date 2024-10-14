<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory; // Use the HasFactory trait for model factories

    // The attributes that are mass assignable
    protected $fillable = ['name', 'phone', 'address', 'email'];

    /**
     * Get the invoices associated with the client.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function invoices()
    {
        // Define a one-to-many relationship with the Invoice model
        return $this->hasMany(Invoice::class);
    }
}
