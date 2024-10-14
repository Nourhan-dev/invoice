<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Invoice extends Model
{
    use HasFactory; // Use the HasFactory trait for model factories

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'client_id', // Foreign key referencing the Client
        'sum',       // Total sum of the invoice
        'status',    // Current status of the invoice (e.g., pending, paid, canceled)
    ];

    /**
     * Get the client associated with the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function client()
    {
        // Define a many-to-one relationship with the Client model
        return $this->belongsTo(Client::class);
    }

    /**
     * Get the items associated with the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function items()
    {
        // Define a one-to-many relationship with the InvoiceItem model
        return $this->hasMany(InvoiceItem::class);
    }

    /**
     * Get the logs associated with the invoice.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function logs()
    {
        // Define a one-to-many relationship with the Log model
        return $this->hasMany(Log::class);
    }
}
