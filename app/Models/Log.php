<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    use HasFactory; // Use the HasFactory trait for model factories

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'action',       // Action performed (e.g., create, update, delete)
        'user_id',      // ID of the user who performed the action
        'role',         // Role of the user at the time of the action
        'invoice_id',   // ID of the related invoice
        'performed_at', // Timestamp when the action was performed
    ];

    /**
     * Get the user that owns the log entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        // Define a many-to-one relationship with the User model
        return $this->belongsTo(User::class);
    }

    /**
     * Get the invoice associated with the log entry.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function invoice()
    {
        // Define a many-to-one relationship with the Invoice model
        return $this->belongsTo(Invoice::class);
    }
}
