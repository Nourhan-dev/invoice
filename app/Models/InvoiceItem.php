<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceItem extends Model
{
    use HasFactory; // Use the HasFactory trait for model factories

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'invoice_id',   // Foreign key referencing the Invoice
        'description',  // Description of the invoice item
        'amount',       // Amount of the invoice item
    ];

    /**
     * Get the invoice that owns the invoice item.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        // Define a many-to-one relationship with the Invoice model
        return $this->belongsTo(Invoice::class);
    }
}
