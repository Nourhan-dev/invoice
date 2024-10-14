<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use Illuminate\Http\Request;
use App\Models\Client;
use Illuminate\Support\Facades\Mail; 
use App\Mail\InvoiceUpdated;
use App\Models\InvoiceItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;  
use App\Models\Log;


class InvoiceApiController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth:api'); // Assuming you're using API authentication
        // Protect specific routes
        // $this->middleware('auth:api')->only(['store', 'update', 'destroy']);
  
    }

    public function index(Request $request)
    {
 
        $user = auth()->user();
   
       if (!$user) {
           return response()->json(['message' => 'User not authenticated.'], 401);
       }
   
       // Get only the roles with guard_name 'api'
       $apiRoles = $user->roles->where('guard_name', 'api');
   
       // Check if any of the user's API roles has the required permission
       $hasPermission = $apiRoles->contains(function ($role) {
           return $role->hasPermissionTo('invoice-list', 'api'); // Specify the guard here
       });
   
       if (!$hasPermission) {
        return response()->json(['error' => 'Sorry, you have no permission '], 403);
    }
   
       // Proceed with the operation if the permission is granted
       // Fetch invoices with client and items relationships
       $invoices = Invoice::with(['client', 'items'])->latest()->paginate(10);

   return response()->json($invoices);

}
    
   
public function show(Invoice $invoice)
{
    // Ensure the user is authenticated
     $user = Auth::guard('api')->user();

    
    if (!$user) {
        return response()->json(['message' => 'User not authenticated.'], 401);
    }

    // Get only the roles with guard_name 'api'
    $apiRoles = $user->roles->where('guard_name', 'api');

    // Check if any of the user's API roles has the required permission
    $hasPermission = $apiRoles->contains(function ($role) {
        return $role->hasPermissionTo('invoice-list', 'api'); // Specify the guard here
    });

    if (!$hasPermission) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    // Load the invoice with items and check the output
    $invoiceWithItems = $invoice->load('items');
    
    // Return invoice with items and debug the response
    return response()->json($invoiceWithItems);
}
    
    
    

    public function store(Request $request)
{
    $user = auth()->user();
   
       if (!$user) {
           return response()->json(['message' => 'User not authenticated.'], 401);
       }
   
       // Get only the roles with guard_name 'api'
       $apiRoles = $user->roles->where('guard_name', 'api');
   
       // Check if any of the user's API roles has the required permission
       $hasPermission = $apiRoles->contains(function ($role) {
           return $role->hasPermissionTo('invoice-create', 'api'); // Specify the guard here
       });
   
       if (!$hasPermission) {
        return response()->json(['error' => 'Sorry, you have no permission '], 403);
    }

    // Validate the input
    $this->validate($request, [
        'client_name' => 'required|string|max:255',
        'client_phone' => 'required|numeric|digits_between:10,15', // Adjust the number of digits as needed
        'client_address' => 'required|string|max:255',
        'client_email' => 'required|email|max:255',
        'description' => 'required|array', // Ensure description is an array
        'description.*' => 'required|string|max:255',
        'amount' => 'required|array', // Ensure amount is an array
        'amount.*' => 'required|numeric',
    ]);

    // Admin-only: create a new client and invoice
    $client = Client::create([
        'name' => $request->client_name,
        'phone' => $request->client_phone,
        'address' => $request->client_address,
        'email' => $request->client_email,
    ]);

    // Calculate the total sum
    $sum = collect($request->amount)->sum();

    // Create the invoice
    $invoice = Invoice::create([
        'client_id' => $client->id,
        'sum' => $sum,
        'status' => 'pending',
    ]);

    // Loop through descriptions and amounts to create items
    foreach ($request->description as $index => $description) {
        // Check if the index exists in the amount array
        $amount = isset($request->amount[$index]) ? $request->amount[$index] : 0; // Default to 0 if not found

        // Create each item
        $invoice->items()->create([
            'description' => $description,
            'amount' => $amount,
        ]);
    }

    $this->logAction('create', $invoice->id);

    // Return the created invoice with all descriptions
    return response()->json($invoice->load('items'), 201);
}

 
    public function update(Request $request, Invoice $invoice)
    {
 
        // Ensure the user is authenticated
        $user = auth()->user();
   
        if (!$user) {
            return response()->json(['message' => 'User not authenticated.'], 401);
        }
    
        // Get only the roles with guard_name 'api'
        $apiRoles = $user->roles->where('guard_name', 'api');

        // dd( $user) ; 
        // Check if any of the user's API roles has the required permission
        $hasPermission = $apiRoles->contains(function ($role) {
            return $role->hasPermissionTo('invoice-edit', 'api'); // Specify the guard here
        });
    
        if (!$hasPermission) {
            return response()->json(['error' => 'Sorry, you have no permission '], 403);
        }

        // Validate incoming request
        $request->validate([
            'client_name' => 'required|string|max:255',
            'client_phone' => 'required|numeric|digits_between:10,15', // Adjust the number of digits as needed
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
        $client = $invoice->client;
        $client->update([
            'name' => $request->client_name,
            'phone' => $request->client_phone,
            'address' => $request->client_address,
            'email' => $request->client_email,
        ]);

        // Update invoice status
        $invoice->update(['status' => $request->status]);

        // Handle existing and new invoice items
        $existingItemIds = $invoice->items()->pluck('id')->toArray();
        foreach ($request->items as $itemData) {
            if (isset($itemData['id']) && in_array($itemData['id'], $existingItemIds)) {
                // Update existing item
                $item = InvoiceItem::find($itemData['id']);
                if ($item) {
                    $item->update($itemData);
                }
            } else {
                // Create new item
                $invoice->items()->create($itemData);
            }
        }

        // Remove items not included in the request
        foreach ($existingItemIds as $existingItemId) {
            if (!collect($request->items)->contains('id', $existingItemId)) {
                InvoiceItem::destroy($existingItemId);
            }
        }

        // Recalculate the total sum of invoice items
        $sum = $invoice->items()->sum('amount');
        $invoice->update(['sum' => $sum]);

        // Detect changes
        $updatedInvoiceData = $invoice->toArray();
        $updatedItemsData = $invoice->items->toArray();
        $changes = $this->detectChanges($originalInvoiceData, $updatedInvoiceData, $originalItemsData, $updatedItemsData);

        // Send email notification with the changes
        
        Mail::to($invoice->client->email)->send(new InvoiceUpdated($invoice, $changes)) ;
        $this->logAction('update', $invoice->id);

        // Return the updated invoice in JSON format
        $invoiceWithItems = $invoice->load('items');
        
        // Return invoice with items and debug the response
        return response()->json($invoice->load('items'), 201);
    }

    // Helper method to detect changes (you can add this method)
    private function detectChanges($originalInvoiceData, $updatedInvoiceData, $originalItemsData, $updatedItemsData)
    {
        $changes = [
            'invoice' => [],
            'items' => []
        ];
    
        // Compare the invoice data
        foreach ($originalInvoiceData as $field => $originalValue) {
            if (isset($updatedInvoiceData[$field]) && $originalValue != $updatedInvoiceData[$field]) {
                $changes['invoice'][$field] = [
                    'old' => $originalValue,
                    'new' => $updatedInvoiceData[$field]
                ];
            }
        }
    
        // Compare each item
        foreach ($originalItemsData as $index => $originalItem) {
            $updatedItem = $updatedItemsData[$index] ?? null;
            if ($updatedItem) {
                foreach ($originalItem as $field => $originalValue) {
                    if (isset($updatedItem[$field]) && $originalValue != $updatedItem[$field]) {
                        $changes['items'][$index][$field] = [
                            'old' => $originalValue,
                            'new' => $updatedItem[$field]
                        ];
                    }
                }
            }
        }
    
        return $changes;
    }
    
    public function destroy(Invoice $invoice)
    {
          // Ensure the user is authenticated
          $user = auth()->user();
   
          if (!$user) {
              return response()->json(['message' => 'User not authenticated.'], 401);
          }
      
          // Get only the roles with guard_name 'api'
          $apiRoles = $user->roles->where('guard_name', 'api');
      
          // Check if any of the user's API roles has the required permission
          $hasPermission = $apiRoles->contains(function ($role) {
              return $role->hasPermissionTo('invoice-delete', 'api'); // Specify the guard here
          });
      
          if (!$hasPermission) {
            return response()->json(['error' => 'Sorry, you have no permission '], 403);
          }
        // Admin-only: delete the invoice
        $invoice->items()->delete();
        // make the invoice sum to 0
        $invoice->update(['sum' => 0 , 'status' => "canceled" ]);

        // Log the action
        $this->logAction('delete', $invoice->id);
        // Load the invoice with items and check the output
        $invoiceWithItems = $invoice->load('items');
            
        // Return invoice with items and debug the response
        return response()->json($invoiceWithItems);  
    
    }

    private function logAction($action, $invoiceId)
   {
       $user = Auth::user();
       $role = $user->roles->pluck('name')->first(); // Assuming Spatie roles

       Log::create([
           'action' => $action,
           'user_id' => $user->id,
           'role' => $role,
           'invoice_id' => $invoiceId,
           'performed_at' => now(),
       ]);
   }

} 