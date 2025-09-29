{{--
    이메일 추적 테이블 뷰
    이메일 열람, 클릭 등의 추적 데이터를 표시
--}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                {{-- 체크박스 컬럼 --}}
                <th scope="col" class="px-3 py-2 text-left">
                    <input type="checkbox"
                           wire:model.live="selectedAll"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                </th>
                
                {{-- 이메일 수신자 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('email_log_id')" class="flex items-center">
                        이메일
                        @if($sortField === 'email_log_id')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                
                {{-- 이벤트 타입 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('event_type')" class="flex items-center">
                        이벤트
                        @if($sortField === 'event_type')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                
                {{-- 클릭 링크 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    링크
                </th>
                
                {{-- 디바이스 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    디바이스
                </th>
                
                {{-- 브라우저 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    브라우저
                </th>
                
                {{-- IP 주소 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    IP 주소
                </th>
                
                {{-- 위치 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    위치
                </th>
                
                {{-- 추적 시간 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('tracked_at')" class="flex items-center">
                        추적 시간
                        @if($sortField === 'tracked_at')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                
                {{-- 액션 컬럼 --}}
                <th scope="col" class="relative px-3 py-2">
                    <span class="sr-only">Actions</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($rows as $item)
            <tr class="hover:bg-gray-50">
                {{-- 체크박스 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <input type="checkbox"
                           wire:model.live="selected"
                           value="{{ $item->id }}"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                </td>
                
                {{-- 이메일 수신자 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <div class="text-xs">
                        @if($item->emailLog)
                            <div class="font-medium text-gray-900">{{ $item->emailLog->to_email }}</div>
                            <div class="text-gray-500">{{ Str::limit($item->emailLog->subject, 30) }}</div>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                </td>
                
                {{-- 이벤트 타입 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @php
                        $eventColors = [
                            'open' => 'bg-blue-100 text-blue-800',
                            'click' => 'bg-green-100 text-green-800',
                            'bounce' => 'bg-red-100 text-red-800',
                            'unsubscribe' => 'bg-yellow-100 text-yellow-800',
                            'spam' => 'bg-gray-100 text-gray-800'
                        ];
                        $eventLabels = [
                            'open' => '열람',
                            'click' => '클릭',
                            'bounce' => '반송',
                            'unsubscribe' => '구독취소',
                            'spam' => '스팸신고'
                        ];
                        $color = $eventColors[$item->event_type] ?? 'bg-gray-100 text-gray-800';
                        $label = $eventLabels[$item->event_type] ?? $item->event_type;
                    @endphp
                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $color }}">
                        {{ $label }}
                    </span>
                    @if($item->count > 1)
                        <span class="ml-1 text-xs text-gray-500">({{ $item->count }})</span>
                    @endif
                </td>
                
                {{-- 클릭 링크 --}}
                <td class="px-3 py-2.5">
                    @if($item->event_type === 'click' && $item->link_url)
                        <div class="text-xs">
                            <div class="text-gray-900 truncate max-w-xs" title="{{ $item->link_url }}">
                                {{ Str::limit($item->link_url, 40) }}
                            </div>
                            @if($item->link_name)
                                <div class="text-gray-500">{{ $item->link_name }}</div>
                            @endif
                        </div>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                
                {{-- 디바이스 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->device_type)
                        @php
                            $deviceIcons = [
                                'desktop' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>',
                                'mobile' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                                'tablet' => '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>'
                            ];
                            $deviceLabels = [
                                'desktop' => '데스크톱',
                                'mobile' => '모바일',
                                'tablet' => '태블릿'
                            ];
                        @endphp
                        <div class="flex items-center text-xs text-gray-700">
                            <span class="text-gray-500">{!! $deviceIcons[$item->device_type] ?? '' !!}</span>
                            <span class="ml-1">{{ $deviceLabels[$item->device_type] ?? $item->device_type }}</span>
                        </div>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                
                {{-- 브라우저 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->browser)
                        <div class="text-xs text-gray-700">
                            {{ $item->browser }}
                            @if($item->os)
                                <div class="text-gray-500">{{ $item->os }}</div>
                            @endif
                        </div>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                
                {{-- IP 주소 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->ip_address)
                        <span class="text-xs font-mono text-gray-700">{{ $item->ip_address }}</span>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                
                {{-- 위치 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->country_code || $item->city)
                        <div class="text-xs text-gray-700">
                            @if($item->country_code)
                                <span class="inline-flex items-center">
                                    <img src="https://flagcdn.com/16x12/{{ strtolower($item->country_code) }}.png" 
                                         class="mr-1" alt="{{ $item->country_code }}">
                                    {{ $item->country_code }}
                                </span>
                            @endif
                            @if($item->city)
                                <div class="text-gray-500">{{ $item->city }}</div>
                            @endif
                        </div>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                
                {{-- 추적 시간 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500">
                    @if($item->tracked_at)
                        {{ \Carbon\Carbon::parse($item->tracked_at)->format('Y-m-d H:i:s') }}
                        <div class="text-gray-400">{{ \Carbon\Carbon::parse($item->tracked_at)->diffForHumans() }}</div>
                    @else
                        -
                    @endif
                </td>
                
                {{-- 액션 버튼들 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-right text-xs font-medium">
                    <div class="flex items-center space-x-1">
                        {{-- 상세 보기 --}}
                        <a href="{{ route('admin.mail.tracking.show', $item->id) }}"
                           class="text-gray-600 hover:text-gray-900"
                           title="상세보기">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        {{-- 삭제 --}}
                        <button wire:click="requestDeleteSingle({{ $item->id }})"
                                class="text-red-600 hover:text-red-900"
                                title="삭제">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="10" class="px-3 py-8 text-center text-sm text-gray-500">
                    추적 데이터가 없습니다.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>