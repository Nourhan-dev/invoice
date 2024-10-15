@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-right">
            @can('invoice-create')
            <a class="btn btn-success btn-sm mb-2" href="{{ route('invoices.create') }}"><i class="fa fa-plus"></i> Create New Invoice</a>
            @endcan
        </div>
    </div>

    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Invoices</h2>
        </div>
        <div class="pull-right mb-3">
            <form action="{{ route('invoices.index') }}" method="GET">
                <div class="input-group">
                    <input type="text" name="client_name" class="mx-1 form-control" placeholder="Client Name" value="{{ request()->input('client_name') }}">
                    <input type="date" name="invoice_date" class="mx-1 form-control" placeholder="Invoice Date" value="{{ request()->input('invoice_date') }}">
                    <input type="number" step="0.01" name="amount" class="mx-1 form-control" placeholder="Amount" value="{{ request()->input('amount') }}">
                    <select name="status" class="mx-1 form-control">
                        <option value="">-- Select Status --</option>
                        <option value="pending" {{ request()->input('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="paid" {{ request()->input('status') == 'paid' ? 'selected' : '' }}>Paid</option>
                        <option value="canceled" {{ request()->input('status') == 'canceled' ? 'selected' : '' }}>Canceled</option>
                    </select>
                    <span class="input-group-btn">
                        <button class="btn btn-primary" type="submit">Search</button>
                    </span>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Display Success Message -->
@if(session('success'))
    <div class="alert alert-success">
        {{ session('success') }}
    </div>
@endif

<!-- Show Invoices Table -->
<table class="table table-bordered">
    <tr>
        <th>No</th>
        <th>Client Name</th>
        <th>Client Phone</th>
        <th>Client Email</th>
        <th>Invoice Date</th>
        <th>Amount</th>
        <th>Status</th>
        <th width="280px">Action</th>
    </tr>
    @foreach ($invoices as $invoice)
    <tr>
        <td>{{ ++$i }}</td>
        <td>{{ $invoice->client->name }}</td>  
        <td>{{ $invoice->client->phone }}</td>  
        <td>{{ $invoice->client->email }}</td>  
        <td>{{ $invoice->created_at->toDateString() }}</td>
        <td>{{ $invoice->sum }}</td>
        <td>{{ ucfirst($invoice->status) }}</td>
        <td>
            @can('invoice-show')  
            <a class="btn btn-primary btn-md" href="{{ route('invoices.show', $invoice->id) }}">Show</a>
            @else
            <a class="btn btn-primary btn-md disable" href="{{ route('invoices.show', $invoice->id) }}">Show</a>
            @endcan
            @can('invoice-edit')  
            <a class="btn btn-primary btn-md" href="{{ route('invoices.edit', $invoice->id) }}">Edit</a>
            @else
            <a class="btn btn-primary btn-md disable" href="{{ route('invoices.edit', $invoice->id) }}">Edit</a>
            @endcan
            @can('invoice-delete')  
             <form action="{{ route('invoices.destroy', $invoice->id) }}" method="POST" style="display:inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger btn-md">Delete</button>
            </form>
            @else
            <button type="submit" class="btn btn-danger btn-md" disabbled>Delete</button>
             @endcan

        </td>
    </tr>
    @endforeach
</table>

{!! $invoices->links() !!}

@endsection
