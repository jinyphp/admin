<!DOCTYPE html>
<html>
<head>
    <title>Test Page</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @livewireStyles
</head>
<body>
    <div class="container mx-auto p-4">
        <h1 class="text-2xl font-bold mb-4">Test Admin Page</h1>
        
        <div class="bg-blue-100 p-4 mb-4">
            <p>This is a simple test page to verify the controller is working.</p>
            <p>JSON Data loaded: {{ isset($jsonData) ? 'Yes' : 'No' }}</p>
            @if(isset($jsonData))
                <p>Title from JSON: {{ $jsonData['title'] ?? 'No title' }}</p>
            @endif
        </div>

        @if(isset($jsonData))
            <div class="mb-4">
                @livewire('jiny-admin::admin-header-with-settings', [
                    'jsonData' => $jsonData,
                    'jsonPath' => $jsonPath ?? null,
                    'mode' => 'index'
                ])
            </div>
        @endif
    </div>
    @livewireScripts
</body>
</html>