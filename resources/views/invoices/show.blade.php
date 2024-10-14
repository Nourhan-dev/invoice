@extends('layouts.app')

@section('content')
<div class="container mb-5">
    <div>
        <div class="card-header bg-dark text-white py-3">
            <div class="d-flex justify-content-between align-items-center">
                <h2 class="mb-0 display-8"><i class="fas fa-file-invoice-dollar mr-2"></i> Invoice #{{ $invoice->id }}</h2>
              
                <span class="display-7 text-danger
                    {{ $invoice->status == 'paid' ? 'badge-success' : ($invoice->status == 'pending' ? 'badge-warning' : 'badge-danger') }}">
                    {{ ucfirst($invoice->status) }}
                </span>
            </div>
        </div>
        <div class="card-body p-4">
            <div class="row mb-4">
                <!-- Client Info -->
                <div class="col-md-6 mb-4">
                    <h5 class="text-uppercase text-secondary"><strong>Client Information</strong></h5>
                    <div class="p-3 border rounded bg-light">
                        <p class="mb-2 fs-5"><strong>Name:</strong> {{ $invoice->client->name }}</p>
                        <p class="mb-2 fs-5"><strong>Phone:</strong> {{ $invoice->client->phone }}</p>
                        <p class="mb-2 fs-5"><strong>Address:</strong> {{ $invoice->client->address }}</p>
                        <p class="mb-2 fs-5"><strong>Email:</strong> {{ $invoice->client->email }}</p>

                    </div>
                </div>
                <!-- Invoice Summary -->
                <div class="col-md-6 mb-4 text-md-right">
                    <h5 class="text-uppercase text-secondary"><strong>Invoice Summary</strong></h5>
                    <div class="p-3 border rounded bg-light">
                        <p class="mb-2 fs-5"><strong>Total Amount:</strong> 
                            <span class="text-success">${{ number_format($invoice->sum, 2) }}</span>
                        </p>
                        <p class="mb-2 fs-5"><strong>Invoice Date:</strong> 
                            {{ $invoice->created_at->format('F j, Y') }}
                        </p>
                      
                    </div>
                </div>
            </div>

            <!-- Invoice Items -->
            <h4 class="text-muted text-uppercase mb-3"><i class="fas fa-list-alt mr-2"></i>Items</h4>
            <table class="table table-hover table-bordered table-sm">
                <thead class="thead-light">
                    <tr>
                        <th scope="col" class="fs-5">#</th>
                        <th scope="col" class="fs-5">Description</th>
                        <th scope="col" class="fs-5">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($invoice->items as $index => $item)
                        <tr>
                            <th scope="row" class="fs-5">{{ $index + 1 }}</th>
                            <td class="fs-5">{{ $item->description }}</td>
                            <td class="fs-5">${{ number_format($item->amount, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <!-- Actions -->
            <div class="d-flex justify-content-between mt-4">
                <a href="{{ route('invoices.index') }}" class="btn btn-secondary">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Invoices
                </a>
                <a href="{{ route('invoices.edit', $invoice->id) }}" class="btn btn-primary">
                    <i class="fas fa-edit mr-2"></i>Edit Invoice
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
