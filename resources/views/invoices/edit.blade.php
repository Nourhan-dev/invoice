@extends('layouts.app')

@section('content')
<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-lg-12 margin-tb">
            <div class="d-flex justify-content-between align-items-center text-end">
                <h2>Edit Invoice</h2>
                <a class="btn btn-primary btn-md" href="{{ route('invoices.index') }}">
                    <i class="fa fa-arrow-left"></i> Back
                </a>
            </div>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Whoops!</strong> There were some problems with your input.<br><br>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('invoices.update', $invoice->id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-4">
            <div class="col-md-4">
                <div class="form-group">
                    <label for="client_name"><strong>Client Name:</strong></label>
                    <input type="text" name="client_name" value="{{ $invoice->client->name }}" class="form-control" placeholder="Client Name" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="client_phone"><strong>Client Phone:</strong></label>
                    <input type="text" name="client_phone" value="{{ $invoice->client->phone }}" class="form-control" placeholder="Client Phone" required>
                </div>
            </div>
            <div class="col-md-4">
                <div class="form-group">
                    <label for="client_address"><strong>Client Address:</strong></label>
                    <input type="text" name="client_address" value="{{ $invoice->client->address }}" class="form-control" placeholder="Client Address" required>
                </div>
            </div>
        </div>
        <div class="col-md-4">

        <div class="form-group">
    <label for="client_email"><strong>Client Email</strong></label>
    <input type="email" name="client_email" class="form-control" value="{{ old('client_email', $invoice->client->email ?? '') }}" placeholder="Enter client's email">
</div>
</div>

        <div class="form-group mb-4">
            <label for="status"><strong>Invoice Status:</strong></label>
            <select name="status" class="form-control" required>
                <option value="pending" {{ $invoice->status == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="paid" {{ $invoice->status == 'paid' ? 'selected' : '' }}>Paid</option>
                <option value="canceled" {{ $invoice->status == 'canceled' ? 'selected' : '' }}>Canceled</option>
            </select>
        </div>

        <h4 class="mt-4">Invoice Items</h4>
        <div id="invoice-items">
            @foreach ($invoice->items as $item)
            <div class="row invoice-item mb-3">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="description"><strong>Description:</strong></label>
                        <input type="text" name="items[{{ $loop->index }}][description]" value="{{ $item->description }}" class="form-control" placeholder="Description" required>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="amount"><strong>Amount:</strong></label>
                        <input type="number" name="items[{{ $loop->index }}][amount]" value="{{ $item->amount }}" class="form-control" placeholder="Amount" required>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-md remove-item">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                </div>
            </div>
            @endforeach
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-secondary btn-md" id="add-item">
                <i class="fa fa-plus"></i> Add Item
            </button>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary btn-md mb-2 mt-2">
                  Update
            </button>
        </div>
    </form>
</div>

<script>
    document.getElementById('add-item').addEventListener('click', function () {
        const itemsContainer = document.getElementById('invoice-items');
        const index = itemsContainer.children.length;
        const newItemHTML = `
            <div class="row invoice-item mb-3">
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="description"><strong>Description:</strong></label>
                        <input type="text" name="items[${index}][description]" class="form-control" placeholder="Description" required>
                    </div>
                </div>
                <div class="col-md-5">
                    <div class="form-group">
                        <label for="amount"><strong>Amount:</strong></label>
                        <input type="number" name="items[${index}][amount]" class="form-control" placeholder="Amount" required>
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="button" class="btn btn-danger btn-md remove-item">
                        <i class="fa-solid fa-trash"></i> Remove
                    </button>
                </div>
            </div>
        `;
        itemsContainer.insertAdjacentHTML('beforeend', newItemHTML);
    });

    // Event delegation for dynamically added items
    document.getElementById('invoice-items').addEventListener('click', function (e) {
        if (e.target.classList.contains('remove-item')) {
            e.target.closest('.invoice-item').remove();
        }
    });
 
    $(document).ready(function() {
        $('#roles').select2({
            placeholder: "Select roles",
            allowClear: true
        });
    });
</script>
 
@endsection
