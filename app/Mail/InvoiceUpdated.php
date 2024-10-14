<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class InvoiceUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public $invoice;
    public $changes;

    /**
     * Create a new message instance.
     *
     * @param Invoice $invoice
     * @param array $changes
     */
    public function __construct(Invoice $invoice, array $changes)
    {
        $this->invoice = $invoice;
        $this->changes = $changes;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
         return $this->subject('Invoice Updated')
                    ->view('emails.invoice_updated');
    }

 
}
