@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-lg-12 margin-tb">
        <div class="pull-left">
            <h2>Add New Invoice</h2>
        </div>
        <div class="pull-right  text-end">
            <a class="btn btn-primary btn-md" href="{{ route('invoices.index') }}"><i class="fa fa-arrow-left"></i> Back</a>
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

<form action="{{ route('invoices.store') }}" method="POST">
    @csrf

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Client Name:</strong>
                <input type="text" name="client_name" class="form-control" placeholder="Client Name" required>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Client Phone:</strong>
                <input type="tel" name="client_phone" class="form-control" placeholder="Client Phone" required>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Client Address:</strong>
                <textarea class="form-control" name="client_address" placeholder="Client Address" required></textarea>
            </div>
        </div>
        <div class="col-xs-12 col-sm-12 col-md-12">
            <div class="form-group">
                <strong>Client Email:</strong>
                <input type="email" name="client_email" class="form-control" value="{{ old('client_email', $invoice->client->client_email ?? '') }}" placeholder=" Client Email">
            </div>
        </div>

        <div id="dynamic-fields">
            <div class="row mb-3">
                <div class="col-md-6">
                    <div class="form-group">
                        <strong>Description:</strong>
                        <input type="text" name="description[]" class="form-control" placeholder="Description" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <strong>Amount:</strong>
                        <input type="number" name="amount[]" class="form-control" placeholder="Amount" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 text-start">
        <button type="button" id="add-field" class="btn btn-secondary mb-3">Add More</button>
        </div>

        <div class="col-xs-12 col-sm-12 col-md-12 text-end">
            <button type="submit" class="btn btn-primary btn-md mb-3 mt-3">  Submit</button>
        </div>
    </div>
</form>

 <script>
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('add-field').addEventListener('click', function() {
            const newRow = document.createElement('div');
            newRow.classList.add('row', 'mb-3');
            newRow.innerHTML = `
                <div class="col-md-6">
                    <div class="form-group">
                        <strong>Description:</strong>
                        <input type="text" name="description[]" class="form-control" placeholder="Description" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <strong>Amount:</strong>
                        <input type="number" name="amount[]" class="form-control" placeholder="Amount" required>
                    </div>
                </div>
            `;
            document.getElementById('dynamic-fields').appendChild(newRow);
        });
    });

    $(document).ready(function() {
        $('#roles').select2({
            placeholder: "Select roles",
            allowClear: true
        });
    });
</script>
  
@endsection
