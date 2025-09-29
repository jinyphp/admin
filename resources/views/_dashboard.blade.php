@extends('jiny-admin::layouts.admin')

@section('title', 'Dashboard - Admin Panel')
@section('page-title', 'Dashboard')

@section('page-header')
<div class="md:flex md:items-center md:justify-between">
    <div class="min-w-0 flex-1">
        <h2 class="text-2xl font-bold leading-7 text-gray-900 dark:text-white sm:truncate sm:text-3xl sm:tracking-tight">
            Dashboard
        </h2>
        <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:space-x-6">
            <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                Welcome back, {{ auth()->user()->name ?? 'Admin' }}
            </div>
        </div>
    </div>
    <div class="mt-4 flex md:ml-4 md:mt-0">
        <button type="button" class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-white shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
            Export
        </button>
        <button type="button" class="ml-3 inline-flex items-center rounded-md bg-indigo-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
            <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path d="M10.75 4.75a.75.75 0 00-1.5 0v4.5h-4.5a.75.75 0 000 1.5h4.5v4.5a.75.75 0 001.5 0v-4.5h4.5a.75.75 0 000-1.5h-4.5v-4.5z" />
            </svg>
            Add New
        </button>
    </div>
</div>
@endsection

@section('content')
<!-- Stats -->
<div class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
    <!-- Stat Card 1 -->
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Total Users</dt>
        <dd class="mt-1 flex items-baseline">
            <div class="flex items-baseline text-2xl font-semibold text-gray-900 dark:text-white">
                12,345
                <span class="ml-2 text-sm font-medium text-green-600">+4.75%</span>
            </div>
        </dd>
    </div>

    <!-- Stat Card 2 -->
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Revenue</dt>
        <dd class="mt-1 flex items-baseline">
            <div class="flex items-baseline text-2xl font-semibold text-gray-900 dark:text-white">
                $54,321
                <span class="ml-2 text-sm font-medium text-green-600">+12.5%</span>
            </div>
        </dd>
    </div>

    <!-- Stat Card 3 -->
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Active Projects</dt>
        <dd class="mt-1 flex items-baseline">
            <div class="flex items-baseline text-2xl font-semibold text-gray-900 dark:text-white">
                89
                <span class="ml-2 text-sm font-medium text-red-600">-2.1%</span>
            </div>
        </dd>
    </div>

    <!-- Stat Card 4 -->
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 px-4 py-5 shadow sm:p-6">
        <dt class="truncate text-sm font-medium text-gray-500 dark:text-gray-400">Completion Rate</dt>
        <dd class="mt-1 flex items-baseline">
            <div class="flex items-baseline text-2xl font-semibold text-gray-900 dark:text-white">
                98.5%
                <span class="ml-2 text-sm font-medium text-green-600">+0.3%</span>
            </div>
        </dd>
    </div>
</div>

<!-- Charts Section -->
<div class="mt-8 grid grid-cols-1 gap-6 lg:grid-cols-2">
    <!-- Chart Card 1 -->
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">Sales Overview</h3>
            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Monthly sales performance</div>
        </div>
        <div class="px-6 pb-6">
            <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded"></div>
        </div>
    </div>

    <!-- Chart Card 2 -->
    <div class="overflow-hidden rounded-lg bg-white dark:bg-gray-800 shadow">
        <div class="p-6">
            <h3 class="text-base font-semibold leading-6 text-gray-900 dark:text-white">User Activity</h3>
            <div class="mt-2 text-sm text-gray-500 dark:text-gray-400">Daily active users</div>
        </div>
        <div class="px-6 pb-6">
            <div class="h-64 bg-gray-100 dark:bg-gray-700 rounded"></div>
        </div>
    </div>
</div>

<!-- Recent Activity Table -->
<div class="mt-8">
    <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
        <table class="min-w-full divide-y divide-gray-300 dark:divide-gray-700">
            <thead class="bg-gray-50 dark:bg-gray-800">
                <tr>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        User
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Action
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Status
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500 dark:text-gray-400">
                        Date
                    </th>
                    <th scope="col" class="relative px-6 py-3">
                        <span class="sr-only">Edit</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-900">
                <tr>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                        Jane Cooper
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        Created new project
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        <span class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800">
                            Completed
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        2 hours ago
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                        <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View</a>
                    </td>
                </tr>
                <tr>
                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900 dark:text-white">
                        Cody Fisher
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        Updated profile
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        <span class="inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800">
                            Pending
                        </span>
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 dark:text-gray-400">
                        3 hours ago
                    </td>
                    <td class="whitespace-nowrap px-6 py-4 text-right text-sm font-medium">
                        <a href="#" class="text-indigo-600 hover:text-indigo-900 dark:text-indigo-400 dark:hover:text-indigo-300">View</a>
                    </td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
@endsection