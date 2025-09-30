{{-- 
    사용자 세션 목록 테이블 뷰
    활성 세션 모니터링 및 관리
--}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-3 py-2 text-left">
                    <input type="checkbox" 
                           wire:model.live="selectedAll"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    ID
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    사용자
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    IP 주소
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    브라우저
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    플랫폼
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    2FA
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('last_activity')" class="flex items-center">
                        활동 시간
                        @if($sortField === 'last_activity')
                            <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="{{ $sortDirection === 'asc' ? 'M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z' : 'M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z' }}" clip-rule="evenodd"/>
                            </svg>
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    상태
                </th>
                <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase">
                    작업
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($rows as $item)
            <tr class="hover:bg-gray-50 transition-colors">
                <td class="px-3 py-2 whitespace-nowrap">
                    <input type="checkbox" 
                           wire:model.live="selected"
                           value="{{ $item->id }}"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-900">
                    {{ $item->id }}
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    @if($item->user)
                        <div class="flex items-center">
                            @if($item->session_id === session()->getId())
                                <span class="inline-flex items-center justify-center w-6 h-6 mr-2 text-xs font-medium text-white bg-green-500 rounded-full">
                                    나
                                </span>
                            @endif
                            <div>
                                <a href="{{ route('admin.system.users.show', $item->user_id) }}" 
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium">
                                    {{ $item->user->name }}
                                </a>
                                <p class="text-xs text-gray-500">{{ $item->user->email }}</p>
                            </div>
                        </div>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600">
                    <span class="font-mono">{{ $item->ip_address ?? '-' }}</span>
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    <div class="flex items-center text-xs text-gray-600">
                        @php
                            $browserIcons = [
                                'Chrome' => '<svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0zm0 2.197c5.41 0 9.803 4.393 9.803 9.803 0 .841-.107 1.658-.308 2.438h-6.852c.417-.72.658-1.556.658-2.448 0-2.646-2.146-4.792-4.792-4.792-1.845 0-3.446 1.045-4.248 2.574L3.169 4.639A9.75 9.75 0 0112 2.197z"/></svg>',
                                'Firefox' => '<svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0z"/></svg>',
                                'Safari' => '<svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0z"/></svg>',
                                'Edge' => '<svg class="w-4 h-4 mr-1" viewBox="0 0 24 24" fill="currentColor"><path d="M12 0C5.373 0 0 5.373 0 12s5.373 12 12 12 12-5.373 12-12S18.627 0 12 0z"/></svg>',
                            ];
                        @endphp
                        {!! $browserIcons[$item->browser] ?? '' !!}
                        {{ $item->browser ?? 'Unknown' }}
                        @if($item->browser_version)
                            <span class="text-gray-400 ml-1">{{ $item->browser_version }}</span>
                        @endif
                    </div>
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600">
                    <div class="flex items-center">
                        @if($item->device === 'Mobile')
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M7 2a2 2 0 00-2 2v12a2 2 0 002 2h6a2 2 0 002-2V4a2 2 0 00-2-2H7zm3 14a1 1 0 100-2 1 1 0 000 2z"/>
                            </svg>
                        @elseif($item->device === 'Tablet')
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M5 2a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V4a2 2 0 00-2-2H5zm5 14a1 1 0 100-2 1 1 0 000 2z"/>
                            </svg>
                        @else
                            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M3 5a2 2 0 012-2h10a2 2 0 012 2v8a2 2 0 01-2 2h-2.22l.123.489.804.804A1 1 0 0113 18H7a1 1 0 01-.707-1.707l.804-.804L7.22 15H5a2 2 0 01-2-2V5z"/>
                            </svg>
                        @endif
                        {{ $item->platform ?? 'Unknown' }}
                    </div>
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    @if($item->two_factor_used)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                            <svg class="w-3 h-3 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 1a4.5 4.5 0 00-4.5 4.5V9H5a2 2 0 00-2 2v6a2 2 0 002 2h10a2 2 0 002-2v-6a2 2 0 00-2-2h-.5V5.5A4.5 4.5 0 0010 1zm3 8V5.5a3 3 0 10-6 0V9h6z" clip-rule="evenodd"/>
                            </svg>
                            사용
                        </span>
                    @else
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            미사용
                        </span>
                    @endif
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-xs text-gray-600">
                    <div class="flex flex-col">
                        <div class="text-gray-900">
                            @if($item->login_at)
                                <span class="text-gray-500">로그인:</span> {{ \Carbon\Carbon::parse($item->login_at)->format('m-d H:i') }}
                            @else
                                <span class="text-gray-500">로그인:</span> -
                            @endif
                        </div>
                        <div class="mt-0.5">
                            @if($item->last_activity)
                                @php
                                    $lastActivity = \Carbon\Carbon::parse($item->last_activity);
                                    $diff = $lastActivity->diffInMinutes(now());
                                @endphp
                                <span class="text-gray-500">활동:</span>
                                @if($diff < 5)
                                    <span class="text-green-600 font-medium">방금 전</span>
                                @elseif($diff < 30)
                                    <span class="text-blue-600">{{ $lastActivity->diffForHumans() }}</span>
                                @else
                                    <span class="text-gray-500">{{ $lastActivity->diffForHumans() }}</span>
                                @endif
                            @else
                                <span class="text-gray-500">활동:</span> -
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-3 py-2 whitespace-nowrap">
                    @if($item->is_active)
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                            <span class="w-1.5 h-1.5 mr-1 bg-green-500 rounded-full animate-pulse"></span>
                            활성
                        </span>
                    @else
                        <span class="inline-flex items-center px-1.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-600">
                            종료됨
                        </span>
                    @endif
                </td>
                <td class="px-3 py-2 whitespace-nowrap text-center">
                    <div class="flex items-center justify-center space-x-1">
                        <a href="{{ route('admin.system.user.sessions.show', $item->id) }}" 
                           class="inline-flex items-center justify-center w-7 h-7 text-gray-500 hover:text-gray-700 hover:bg-gray-100 rounded transition-colors"
                           title="상세보기">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        
                        @if($item->is_active && $item->session_id !== session()->getId())
                            <button wire:click="terminateSession({{ $item->id }})"
                                    class="inline-flex items-center justify-center w-7 h-7 text-red-500 hover:text-red-700 hover:bg-red-50 rounded transition-colors"
                                    title="세션 종료">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            </button>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="px-6 py-8 text-center">
                    <div class="flex flex-col items-center">
                        <svg class="w-12 h-12 text-gray-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <p class="text-gray-500 text-sm">활성 세션이 없습니다.</p>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

