{{--
    AdminUsers Modern Table View
    Based on Tailwind UI "Product Marketing" / Dashboard aesthetics
--}}
<div class="bg-white shadow-sm ring-1 ring-gray-900/5 sm:rounded-xl overflow-hidden">
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th scope="col" class="px-4 py-3.5 text-left text-sm font-semibold text-gray-900">
                        <div class="flex items-center">
                            <input type="checkbox" wire:model.live="selectedAll"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded">
                        </div>
                    </th>
                    <th scope="col"
                        class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900 uppercase tracking-wide">
                        <button wire:click="sortBy('id')" class="group inline-flex items-center">
                            ID
                            <span class="ml-2 flex-none rounded text-gray-400 group-hover:visible group-focus:visible">
                                @if ($sortField === 'id')
                                    @if ($sortDirection === 'asc')
                                        <svg class="h-4 w-4 text-gray-900" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @else
                                        <svg class="h-4 w-4 text-gray-900" viewBox="0 0 20 20" fill="currentColor">
                                            <path fill-rule="evenodd"
                                                d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z"
                                                clip-rule="evenodd" />
                                        </svg>
                                    @endif
                                @else
                                    <svg class="h-4 w-4 invisible group-hover:visible" viewBox="0 0 20 20"
                                        fill="currentColor">
                                        <path fill-rule="evenodd"
                                            d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z"
                                            clip-rule="evenodd" />
                                    </svg>
                                @endif
                            </span>
                        </button>
                    </th>
                    <th scope="col"
                        class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900 uppercase tracking-wide">
                        <button wire:click="sortBy('name')" class="group inline-flex items-center">
                            User
                            <!-- Sort Icon Logic (Simplified for brevity, same as above) -->
                        </button>
                    </th>
                    <th scope="col"
                        class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900 uppercase tracking-wide">
                        Role & Status
                    </th>
                    <th scope="col"
                        class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900 uppercase tracking-wide">
                        Security
                    </th>
                    <th scope="col"
                        class="px-3 py-3.5 text-left text-xs font-semibold text-gray-900 uppercase tracking-wide">
                        Activity
                    </th>
                    <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                        <span class="sr-only">Actions</span>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 bg-white">
                @forelse($rows as $item)
                    <tr class="hover:bg-gray-50 transition-colors duration-200">
                        <td class="px-4 py-4 whitespace-nowrap">
                            <input type="checkbox" wire:model.live="selected" value="{{ $item->id }}"
                                class="h-4 w-4 text-indigo-600 focus:ring-indigo-600 border-gray-300 rounded">
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                            #{{ $item->id }}
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            <div class="flex items-center">
                                <div class="h-10 w-10 flex-shrink-0">
                                    @if (!empty($item->avatar))
                                        <img class="h-10 w-10 rounded-full object-cover ring-2 ring-white"
                                            src="{{ $item->avatar }}" alt="{{ $item->name }}">
                                    @else
                                        <div class="h-10 w-10 rounded-full flex items-center justify-center text-white font-bold text-sm ring-2 ring-white shadow-sm"
                                            style="background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%);">
                                            {{ mb_substr($item->name ?? 'U', 0, 1) }}
                                        </div>
                                    @endif
                                </div>
                                <div class="ml-4">
                                    <div class="font-medium text-gray-900">{{ $item->name }}</div>
                                    <div class="text-gray-500 text-xs">{{ $item->email }}</div>
                                    @if (!empty($item->username))
                                        <div class="text-gray-400 text-xs mt-0.5">@{{ $item - > username }}</div>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <!-- User Type / Admin Badge -->
                                <div class="flex items-center space-x-2">
                                    @if (isset($item->utype_name) && $item->utype_name)
                                        <span
                                            class="inline-flex items-center rounded-md bg-purple-50 px-2 py-1 text-xs font-medium text-purple-700 ring-1 ring-inset ring-purple-700/10">
                                            {{ $item->utype_name }}
                                        </span>
                                    @elseif($item->isAdmin)
                                        <span
                                            class="inline-flex items-center rounded-md bg-indigo-50 px-2 py-1 text-xs font-medium text-indigo-700 ring-1 ring-inset ring-indigo-700/10">
                                            Admin
                                        </span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-md bg-gray-50 px-2 py-1 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">
                                            User
                                        </span>
                                    @endif
                                </div>

                                <!-- Verification Status -->
                                <div class="flex items-center">
                                    @if ($item->email_verified_at)
                                        <span class="inline-flex items-center gap-x-1.5 text-xs text-green-700">
                                            <svg class="h-1.5 w-1.5 fill-green-500" viewBox="0 0 6 6"
                                                aria-hidden="true">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                            Verified
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-x-1.5 text-xs text-yellow-700">
                                            <svg class="h-1.5 w-1.5 fill-yellow-500" viewBox="0 0 6 6"
                                                aria-hidden="true">
                                                <circle cx="3" cy="3" r="3" />
                                            </svg>
                                            Unverified
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap">
                            <div class="flex flex-col space-y-1">
                                <!-- 2FA Status -->
                                <div class="flex items-center justify-between max-w-[100px]">
                                    <span class="text-xs text-gray-500">2FA</span>
                                    @if ($item->two_factor_enabled)
                                        <span
                                            class="inline-flex items-center rounded-full bg-green-50 px-1.5 py-0.5 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20">ON</span>
                                    @else
                                        <span
                                            class="inline-flex items-center rounded-full bg-gray-50 px-1.5 py-0.5 text-xs font-medium text-gray-600 ring-1 ring-inset ring-gray-500/10">OFF</span>
                                    @endif
                                </div>

                                <!-- Password Expiry -->
                                @php
                                    $user = \Jiny\Admin\Models\User::find($item->id);
                                    $passwordStatus = $user ? $user->password_expiry_status : 'active';
                                @endphp
                                <div class="flex items-center justify-between max-w-[100px]">
                                    <span class="text-xs text-gray-500">Pwd</span>
                                    @if ($passwordStatus === 'expired')
                                        <span class="text-xs text-red-600 font-medium">Expired</span>
                                    @elseif($passwordStatus === 'expiring_soon')
                                        <span class="text-xs text-yellow-600 font-medium">Soon</span>
                                    @else
                                        <span class="text-xs text-green-600 font-medium">Valid</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-3 py-4 whitespace-nowrap text-sm text-gray-500">
                            <div class="flex flex-col">
                                <span class="text-xs">Joined:
                                    {{ \Carbon\Carbon::parse($item->created_at)->format('M d, Y') }}</span>
                                <span class="text-xs mt-0.5">Logins: <span
                                        class="font-medium text-gray-900">{{ $item->login_count ?? 0 }}</span></span>
                            </div>
                        </td>
                        <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('admin.system.users.edit', $item->id) }}"
                                    class="text-indigo-600 hover:text-indigo-900 bg-indigo-50 hover:bg-indigo-100 p-1.5 rounded-md transition-colors">
                                    <span class="sr-only">Edit, {{ $item->name }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z">
                                        </path>
                                    </svg>
                                </a>
                                <button wire:click="requestDeleteSingle({{ $item->id }})"
                                    class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 p-1.5 rounded-md transition-colors">
                                    <span class="sr-only">Delete, {{ $item->name }}</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16">
                                        </path>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-3 py-8 text-center text-sm text-gray-500">
                            <div class="flex flex-col items-center justify-center">
                                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                </svg>
                                <span class="mt-2 block font-medium text-gray-900">No users found</span>
                                <p class="mt-1 text-gray-500">Get started by creating a new user.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
