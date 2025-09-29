@extends('jiny-admin::layouts.admin')

@section('title', 'Test Page')

@section('content')
<div class="bg-white dark:bg-gray-800 shadow rounded-lg p-6">
    <h1 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Test Page</h1>
    <p class="text-gray-600 dark:text-gray-300">This is a test content. If you can see this, the layout is working correctly.</p>
    <p class="mt-4 text-blue-600 dark:text-blue-400">AAA - Your test text is here!</p>
</div>
@endsection