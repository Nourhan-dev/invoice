<!DOCTYPE html>
<html>
<head>
    <title>Invoice Updated</title>
</head>
<body>
    <h1>Invoice Update Notification</h1>
    
    <p>Dear {{ $invoice->client->name }},</p>
    
    <p>Your invoice (ID: {{ $invoice->id }}) has been updated. Below are the details of the changes:</p>

    <h3>Invoice Changes:</h3>
    
    <ul>
        @foreach($changes['invoice'] ?? [] as $field => $change)
            <li><strong>{{ ucfirst($field) }}:</strong> {{ $change['old'] }} → {{ $change['new'] }}</li>
        @endforeach
    </ul>

    <h3>Item Changes:</h3>
    <ul>
        @foreach($changes['items'] ?? [] as $index => $itemChanges)
            <li><strong>Item #{{ $index + 1 }}:</strong></li>
            <ul>
                @foreach($itemChanges as $field => $change)
                    <li><strong>{{ ucfirst($field) }}:</strong> {{ $change['old'] }} → {{ $change['new'] }}</li>
                @endforeach
            </ul>
        @endforeach
    </ul>

    <p>Thank you for your attention!</p>
</body>
</html>
