{{--
    Admin User Logs 테이블 뷰
    로그인/로그아웃 활동 로그 표시
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
                    이메일
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    이름
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    <button wire:click="sortBy('action')" class="flex items-center hover:text-gray-900 dark:hover:text-white transition-colors">
                        액션
                        @if($sortField === 'action')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' }}" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    IP 주소
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    2FA
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 dark:text-gray-300 uppercase">
                    <button wire:click="sortBy('logged_at')" class="flex items-center hover:text-gray-900 dark:hover:text-white transition-colors">
                        시간
                        @if($sortField === 'logged_at')
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
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900 dark:text-gray-300">
                    {{ $item->id }}
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->user_id)
                        <a href="{{ route('admin.user.logs.show', $item->id) }}"
                           class="text-xs text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium hover:underline">
                            {{ $item->email }}
                        </a>
                    @else
                        <a href="{{ route('admin.user.logs.show', $item->id) }}"
                           class="text-xs text-gray-700 hover:text-gray-900 dark:text-gray-400 dark:hover:text-gray-200 hover:underline">
                            {{ $item->email }}
                        </a>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->user_id && $item->name)
                        <a href="{{ route('admin.users.show', $item->user_id) }}"
                           class="text-xs text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium hover:underline">
                            {{ $item->name }}
                        </a>
                    @else
                        <span class="text-xs text-gray-500 dark:text-gray-400">{{ $item->name ?? '-' }}</span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @php
                        $colors = [
                            'login' => 'green',
                            'logout' => 'blue',
                            'failed_login' => 'red',
                            'unauthorized_login' => 'orange',
                            'test_login' => 'yellow'
                        ];
                        $labels = [
                            'login' => '로그인',
                            'logout' => '로그아웃',
                            'failed_login' => '로그인 실패',
                            'unauthorized_login' => '권한 없음',
                            'test_login' => '테스트'
                        ];
                        $color = $colors[$item->action] ?? 'gray';
                        $label = $labels[$item->action] ?? $item->action;
                    @endphp
                    @if($color == 'green')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300">
                    @elseif($color == 'blue')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                    @elseif($color == 'red')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                    @elseif($color == 'orange')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300">
                    @elseif($color == 'yellow')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-300">
                    @endif
                        {{ $label }}
                    </span>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                    {{ $item->ip_address ?? '-' }}
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->action === 'login')
                        @if($item->two_factor_used)
                            <div class="flex items-center space-x-1">
                                <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300">
                                    <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                                    </svg>
                                    사용
                                </span>
                                @if($item->two_factor_method)
                                    <span class="text-xs text-gray-500 dark:text-gray-400">
                                        ({{ $item->two_factor_method }})
                                    </span>
                                @endif
                            </div>
                        @elseif($item->two_factor_required)
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300">
                                <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                필요
                            </span>
                        @else
                            <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                미사용
                            </span>
                        @endif
                    @elseif($item->action === 'failed_login' && $item->two_factor_attempts)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300">
                            실패 {{ $item->two_factor_attempts }}회
                        </span>
                    @else
                        <span class="text-xs text-gray-400 dark:text-gray-500">-</span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-600 dark:text-gray-400">
                    @if($item->logged_at)
                        {{ \Carbon\Carbon::parse($item->logged_at)->format('Y-m-d H:i:s') }}
                    @elseif($item->created_at)
                        {{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d H:i:s') }}
                    @else
                        -
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-right">
                    <div class="flex items-center justify-end space-x-1">
                        <a href="{{ route('admin.user.logs.show', $item->id) }}"
                           class="text-xs text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300 font-medium">보기</a>
                        <span class="text-gray-300 dark:text-gray-600">|</span>
                        <button wire:click="requestDeleteSingle({{ $item->id }})"
                                class="text-xs text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300 font-medium">삭제</button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-3 py-8 text-center">
                    <div class="flex flex-col items-center justify-center">
                        <svg class="w-12 h-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        <p class="text-sm text-gray-500 dark:text-gray-400">로그 데이터가 없습니다</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>