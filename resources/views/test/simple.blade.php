<!DOCTYPE html>
<html>
<head>
    <title>Admin Test Page</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
    </style>
</head>
<body>
    <h1>Admin Test Management</h1>
    <p>This is a simple test view to verify the page is working.</p>
    
    @if(isset($error))
    <div style="background: #fee; border: 1px solid #f00; padding: 10px; margin: 10px 0;">
        <strong>Error:</strong> {{ $error }}
    </div>
    @endif
    
    <h2>Configuration Info:</h2>
    <ul>
        <li>Title: {{ $jsonData['title'] ?? 'Not set' }}</li>
        <li>Route: {{ $jsonData['route']['name'] ?? 'Not set' }}</li>
        <li>Table: {{ $jsonData['table']['name'] ?? 'Not set' }}</li>
    </ul>
    
    <h2>Test Data:</h2>
    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Title</th>
                <th>Description</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @php
                $testData = \DB::table('admin_tests')->get();
            @endphp
            @foreach($testData as $item)
            <tr>
                <td>{{ $item->id }}</td>
                <td>{{ $item->title }}</td>
                <td>{{ $item->description }}</td>
                <td>{{ $item->enable ? 'Enabled' : 'Disabled' }}</td>
                <td>
                    <a href="/admin/test/{{ $item->id }}/edit">Edit</a> |
                    <a href="/admin/test/{{ $item->id }}">View</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    
    <p><a href="/admin/test/create">Create New Test</a></p>
</body>
</html>