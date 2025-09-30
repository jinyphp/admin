{{--
    Admin Password Logs 테이블 뷰
    비밀번호 시도 실패 및 차단된 IP 관리
--}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
        <thead class="bg-gray-50 dark:bg-gray-800">
            <tr>
                <th scope="col" class="px-3 py-2 text-left">
                    <input type="checkbox"
                           wire:model.live="selectedAll"
                           class="h-4 w-4 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 dark:bg-gray-700">
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    <button wire:click="sortBy('id')" class="flex items-center hover:text-gray-900 dark:hover:text-white transition-colors">
                        ID
                        @if($sortField === 'id')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' }}" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    <button wire:click="sortBy('email')" class="flex items-center hover:text-gray-900 dark:hover:text-white transition-colors">
                        이메일
                        @if($sortField === 'email')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' }}" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    <button wire:click="sortBy('ip_address')" class="flex items-center hover:text-gray-900 dark:hover:text-white transition-colors">
                        IP 주소
                        @if($sortField === 'ip_address')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' }}" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase hidden lg:table-cell">
                    브라우저
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    <button wire:click="sortBy('attempt_count')" class="flex items-center hover:text-gray-900 dark:hover:text-white transition-colors">
                        시도
                        @if($sortField === 'attempt_count')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' }}" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    <button wire:click="sortBy('status')" class="flex items-center hover:text-gray-900 dark:hover:text-white transition-colors">
                        상태
                        @if($sortField === 'status')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' }}" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase hidden md:table-cell">
                    <button wire:click="sortBy('last_attempt_at')" class="flex items-center hover:text-gray-900 dark:hover:text-white transition-colors">
                        마지막 시도
                        @if($sortField === 'last_attempt_at')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' }}" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </th>
                <th scope="col" class="relative px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    작업
                </th>
            </tr>
        </thead>
        <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
            @forelse($rows as $item)
            <tr class="hover:bg-gray-50 dark:hover:bg-gray-800">
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <input type="checkbox"
                           wire:model.live="selected"
                           value="{{ $item->id }}"
                           class="h-3.5 w-3.5 text-blue-600 border-gray-300 dark:border-gray-600 rounded focus:ring-1 focus:ring-blue-500 dark:bg-gray-700">
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <span class="text-xs text-gray-600 dark:text-gray-400 font-mono">
                        {{ $item->id }}
                    </span>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->user_id)
                        <a href="{{ route('admin.system.users.show', $item->user_id) }}"
                           class="text-xs text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium hover:underline">
                            {{ $item->email }}
                        </a>
                    @else
                        <span class="text-xs text-gray-700 dark:text-gray-300">
                            {{ $item->email }}
                        </span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <div class="flex items-center space-x-1">
                        <span class="text-xs text-gray-600 dark:text-gray-400 font-mono">
                            {{ $item->ip_address }}
                        </span>
                    </div>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap hidden lg:table-cell">
                    @if($item->browser)
                        <span class="text-xs text-gray-600 dark:text-gray-400" title="{{ $item->user_agent }}">
                            {{ Str::limit($item->browser, 30) }}
                        </span>
                    @else
                        <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @php
                        $attemptColor = 'gray';
                        if ($item->attempt_count >= 5) {
                            $attemptColor = 'red';
                        } elseif ($item->attempt_count >= 3) {
                            $attemptColor = 'yellow';
                        }
                    @endphp
                    @if($attemptColor == 'red')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                    @elseif($attemptColor == 'yellow')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                    @endif
                        {{ $item->attempt_count }}회
                    </span>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @php
                        $statusColors = [
                            'failed' => 'yellow',
                            'blocked' => 'red',
                            'resolved' => 'green'
                        ];
                        $statusLabels = [
                            'failed' => '실패',
                            'blocked' => '차단됨',
                            'resolved' => '해결됨'
                        ];
                        $statusColor = $statusColors[$item->status] ?? 'gray';
                        $statusLabel = $statusLabels[$item->status] ?? $item->status;
                    @endphp
                    @if($statusColor == 'red')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                            </svg>
                    @elseif($statusColor == 'yellow')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                    @elseif($statusColor == 'green')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                    @endif
                        {{ $statusLabel }}
                    </span>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400 hidden md:table-cell">
                    @if($item->last_attempt_at)
                        <div class="flex flex-col">
                            <span>{{ \Carbon\Carbon::parse($item->last_attempt_at)->format('Y-m-d') }}</span>
                            <span class="text-xs text-gray-500">{{ \Carbon\Carbon::parse($item->last_attempt_at)->format('H:i:s') }}</span>
                        </div>
                    @else
                        -
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-right">
                    <div class="flex items-center justify-end space-x-1">
                        @if($item->status === 'blocked')
                            <button wire:click="hookCustom('UnblockIP', {{ $item->id }})"
                                    class="text-xs text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300 font-medium">
                                <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                                </svg>
                                차단해제
                            </button>
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                        @endif
                        <a href="{{ route('admin.system.user.password.logs.show', $item->id) }}"
                           class="text-xs text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium">보기</a>
                        @if($item->status !== 'blocked')
                            <span class="text-gray-300 dark:text-gray-600">|</span>
                            <button wire:click="requestDeleteSingle({{ $item->id }})"
                                    class="text-xs text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">삭제</button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-3 py-8 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">비밀번호 로그 데이터가 없습니다</p>
                        <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">시스템이 정상적으로 작동하고 있습니다</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    {{-- Bulk Actions for selected items --}}
    @if(count($selected) > 0 && $rows->where('status', 'blocked')->count() > 0)
    <div class="bg-gray-50 dark:bg-gray-800 px-4 py-3 border-t border-gray-200 dark:border-gray-700">
        <div class="flex items-center justify-between">
            <span class="text-xs text-gray-600 dark:text-gray-400">
                {{ count($selected) }}개 항목 선택됨
            </span>
            <div class="flex space-x-2">
                @if($rows->whereIn('id', $selected)->where('status', 'blocked')->count() > 0)
                <button wire:click="bulkUnblock"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-green-600 rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"></path>
                    </svg>
                    선택 항목 차단해제
                </button>
                @endif
                <button wire:click="bulkDelete"
                        class="px-3 py-1.5 text-xs font-medium text-white bg-red-600 rounded-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                    <svg class="w-3.5 h-3.5 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    선택 항목 삭제
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
