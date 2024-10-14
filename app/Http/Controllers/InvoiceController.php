<?php

namespace App\Http\Controllers;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Client;
use App\Models\Log;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  
use Illuminate\Support\Facades\Mail; 
use App\Mail\InvoiceUpdated;

class InvoiceController extends Controller
{
    // Apply permissions middleware for different actions
    public function __construct()
    {
        $this->middleware('permission:invoice-list|invoice-create|invoice-edit|invoice-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:invoice-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:invoice-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:invoice-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a list of invoices with optional filters for client, date, amount, and status.
     */
    public function index(Request $request)
    {
        // Fetch filters from request
        $client_name = $request->input('client_name');
        $invoice_date = $request->input('invoice_date');
        $amount = $request->input('amount');
        $status = $request->input('status');

        // Build query with conditions
        $query = Invoice::query();

        if ($client_name) {
            $query->whereHas('client', function ($q) use ($client_name) {
                $q->where('name', 'like', "%{$client_name}%");
            });
        }

        if ($invoice_date) {
            $query->whereDate('created_at', $invoice_date);
        }

        if ($amount) {
            $query->where('sum', '=', $amount);
        }

        if ($status) {
            $query->where('status', $status);
        }

        // Paginate results
        $invoices = $query->latest()->paginate(10);

        return view('invoices.index', compact('invoices'))
            ->with('i', (request()->input('page', 1) - 1) * 10);
    }

    /**
     * Show the form to create a new invoice.
     */
    public function create(): View
    {
        return view('invoices.create');
    }

    /**
     * Store a newly created invoice and its associated items.
     */
    public function store(Request $request): RedirectResponse
    {
        // Validate incoming data
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|numeric|digits_between:10,15',
            'client_address' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'description.*' => 'required|string|max:255',
            'amount.*' => 'required|numeric',
        ]);

        // Create client record
        $client = Client::create([
            'name' => $validated['client_name'],
            'phone' => $validated['client_phone'],
            'address' => $validated['client_address'],
            'email' => $validated['client_email'],
        ]);

        // Calculate total sum
        $sum = collect($request->amount)->sum();

        // Create the invoice
        $invoice = Invoice::create([
            'client_id' => $client->id,
            'sum' => $sum,
            'status' => $request->status ?? 'pending',
        ]);

        // Add invoice items
        foreach ($request->description as $index => $description) {
            if (!empty($description) && isset($request->amount[$index])) {
                $invoice->items()->create([
                    'description' => $description,
                    'amount' => $request->amount[$index],
                ]);
            }
        }

        // Log the action
        $this->logAction('create', $invoice->id);

        return redirect()->route('invoices.index')->with('success', 'Invoice created successfully');
    }

    /**
     * Display the details of a specific invoice.
     */
    public function show(Invoice $invoice): View
    {
        $invoice->load('items');
        return view('invoices.show', compact('invoice'));
    }

    /**
     * Show the form to edit an existing invoice.
     */
    public function edit(Invoice $invoice): View
    {
        return view('invoices.edit', compact('invoice'));
    }

    /**
     * Update an existing invoice and its associated items.
     */
    public function update(Request $request, Invoice $invoice): RedirectResponse
    {
        // Validate incoming data
        $validated = $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|numeric|digits_between:10,15',
            'client_address' => 'required|string|max:255',
            'client_email' => 'required|email|max:255',
            'status' => 'required|string|max:255',
            'items.*.id' => 'sometimes|exists:invoice_items,id',
            'items.*.description' => 'required|string|max:255',
            'items.*.amount' => 'required|numeric',
        ]);

        // Track original data for change detection
        $originalInvoiceData = $invoice->toArray();
        $originalItemsData = $invoice->items->toArray();

        // Update client details
        $invoice->client->update([
            'name' => $validated['client_name'],
            'phone' => $validated['client_phone'],
            'address' => $validated['client_address'],
            'email' => $validated['client_email'],
        ]);

        // Update invoice status
        $invoice->update(['status' => $validated['status']]);

        // Handle invoice items (update or create new ones)
        $existingItemIds = $invoice->items()->pluck('id')->toArray();
        foreach ($request->items as $itemData) {
            if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                InvoiceItem::find($itemData['id'])->update($itemData);
            } else {
                $invoice->items()->create($itemData);
            }
        }

        // Remove deleted items
        foreach ($existingItemIds as $existingItemId) {
            if (!collect($request->items)->contains('id', $existingItemId)) {
                InvoiceItem::destroy($existingItemId);
            }
        }

        // Recalculate and update the total sum
        $sum = $invoice->items()->sum('amount');
        $invoice->update(['sum' => $sum]);

        // Detect changes and send email notification
        $updatedInvoiceData = $invoice->toArray();
        $updatedItemsData = $invoice->items->toArray();
        $changes = $this->detectChanges($originalInvoiceData, $updatedInvoiceData, $originalItemsData, $updatedItemsData);

        Mail::to($invoice->client->email)->send(new InvoiceUpdated($invoice, $changes));

        // Log the action
        $this->logAction('update', $invoice->id);

        return redirect()->route('invoices.index')->with('success', 'Invoice updated successfully');
    }

    /**
     * Cancel an invoice and set its sum to zero without deleting.
     */
    public function destroy(Invoice $invoice): RedirectResponse
    {
        // Mark invoice as canceled and set sum to 0
        $invoice->update(['sum' => 0, 'status' => 'canceled']);
        $invoice->items()->delete(); // Delete associated items

        // Log the action
        $this->logAction('delete', $invoice->id);

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice canceled successfully');
    }

    /**
     * Detect changes between original and updated invoice data.
     */
    protected function detectChanges(array $originalInvoiceData, array $updatedInvoiceData, array $originalItemsData, array $updatedItemsData): array
    {
        $changes = [];

        // Check for invoice-level changes
        foreach ($originalInvoiceData as $key => $value) {
            if ($updatedInvoiceData[$key] != $value) {
                $changes['invoice'][$key] = ['old' => $value, 'new' => $updatedInvoiceData[$key]];
            }
        }

        // Check for item-level changes
        foreach ($originalItemsData as $index => $originalItem) {
            $updatedItem = $updatedItemsData[$index] ?? null;
            if ($updatedItem) {
                foreach ($originalItem as $key => $value) {
                    if ($updatedItem[$key] != $value) {
                        $changes['items'][$index][$key] = ['old' => $value, 'new' => $updatedItem[$key]];
                    }
                }
            }
        }

        return $changes;
    }

    /**
     * Log the user's action on an invoice.
     */
    private function logAction(string $action, int $invoiceId): void
    {
        $user = Auth::user();
        $role = $user->roles->pluck('name')->first(); // Assuming Spatie roles are used

        Log::create([
            'action' => $action,
            'user_id' => $user->id,
            'role' => $role,
            'invoice_id' => $invoiceId,
            'performed_at' => now(),
        ]);
    }
}
