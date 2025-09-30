{{--
    이메일 발송 로그 테이블 뷰
    
    @package jiny/admin
    @subpackage admin_email_logs
    @description 이메일 로그 목록을 테이블 형식으로 표시합니다.
                체크박스, 정렬, 페이징 기능을 포함하며 Livewire로 동적 업데이트됩니다.
                Tailwind CSS 스타일 적용 및 반응형 디자인을 지원합니다.
    @version 1.0
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
                
                {{-- ID 컬럼 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('id')" class="flex items-center">
                        ID
                        @if($sortField === 'id')
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
                
                {{-- 수신자 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('to_email')" class="flex items-center">
                        수신자
                        @if($sortField === 'to_email')
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
                
                {{-- 제목 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    제목
                </th>
                
                {{-- 템플릿 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase hidden lg:table-cell">
                    템플릿
                </th>
                
                {{-- 상태 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('status')" class="flex items-center">
                        상태
                        @if($sortField === 'status')
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
                
                {{-- 발송시간 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase hidden md:table-cell">
                    <button wire:click="sortBy('sent_at')" class="flex items-center">
                        발송시간
                        @if($sortField === 'sent_at')
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
                
                {{-- 열람 --}}
                <th scope="col" class="px-3 py-2 text-center text-xs font-medium text-gray-600 uppercase hidden lg:table-cell">
                    열람
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
                
                {{-- ID --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                    {{ $item->id }}
                </td>
                
                {{-- 수신자 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <div class="text-xs">
                        <div class="text-gray-900">{{ $item->to_email }}</div>
                        @if($item->to_name)
                            <div class="text-gray-500">{{ $item->to_name }}</div>
                        @endif
                    </div>
                </td>
                
                {{-- 제목 --}}
                <td class="px-3 py-2.5">
                    <div class="text-xs text-gray-900 truncate max-w-xs">
                        {{ \Illuminate\Support\Str::limit($item->subject, 50) }}
                    </div>
                </td>
                
                {{-- 템플릿 --}}
                <td class="px-3 py-2.5 whitespace-nowrap hidden lg:table-cell">
                    @if($item->template_id)
                        <span class="text-xs text-gray-600">
                            {{ $item->template->name ?? 'Template #'.$item->template_id }}
                        </span>
                    @else
                        <span class="text-xs text-gray-400">-</span>
                    @endif
                </td>
                
                {{-- 상태 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @php
                        $statusColors = [
                            'pending' => 'bg-gray-100 text-gray-800',
                            'processing' => 'bg-blue-100 text-blue-800',
                            'sent' => 'bg-green-100 text-green-800',
                            'failed' => 'bg-red-100 text-red-800',
                            'bounced' => 'bg-yellow-100 text-yellow-800',
                            'opened' => 'bg-indigo-100 text-indigo-800',
                            'clicked' => 'bg-purple-100 text-purple-800'
                        ];
                        $statusLabels = [
                            'pending' => '대기중',
                            'processing' => '처리중',
                            'sent' => '발송완료',
                            'failed' => '실패',
                            'bounced' => '반송',
                            'opened' => '열람',
                            'clicked' => '클릭'
                        ];
                        $color = $statusColors[$item->status] ?? 'bg-gray-100 text-gray-800';
                        $label = $statusLabels[$item->status] ?? $item->status;
                    @endphp
                    <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full {{ $color }}">
                        {{ $label }}
                    </span>
                </td>
                
                {{-- 발송시간 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500 hidden md:table-cell">
                    @if($item->sent_at)
                        {{ \Carbon\Carbon::parse($item->sent_at)->format('Y-m-d H:i') }}
                    @else
                        -
                    @endif
                </td>
                
                {{-- 열람 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-center text-xs text-gray-900 hidden lg:table-cell">
                    @if(isset($item->open_count) && $item->open_count > 0)
                        <span class="font-medium">{{ $item->open_count }}</span>
                    @else
                        <span class="text-gray-400">0</span>
                    @endif
                </td>
                
                {{-- 액션 버튼들 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-right text-xs font-medium">
                    <div class="flex items-center justify-end space-x-1">
                        {{-- 보기 --}}
                        <a href="{{ route('admin.system.mail.logs.show', $item->id) }}"
                           class="text-gray-600 hover:text-gray-900"
                           title="상세보기">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        
                        {{-- 수정 (대기중 상태인 경우만) --}}
                        @if($item->status === 'pending')
                        <a href="{{ route('admin.system.mail.logs.edit', $item->id) }}"
                           class="text-blue-600 hover:text-blue-900"
                           title="수정">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        @endif
                        
                        {{-- 발송 (대기중 상태인 경우만) --}}
                        @if($item->status === 'pending')
                        <button wire:click="hookCustom('send', {'id': {{ $item->id }}})"
                                wire:confirm="이 이메일을 발송하시겠습니까?"
                                wire:loading.attr="disabled"
                                wire:target="hookCustom"
                                class="text-green-600 hover:text-green-900 disabled:opacity-50 disabled:cursor-not-allowed relative"
                                title="발송">
                            <svg wire:loading.remove wire:target="hookCustom" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            <svg wire:loading wire:target="hookCustom" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                        @endif
                        
                        {{-- 재발송 (실패/반송 상태인 경우만) --}}
                        @if(in_array($item->status, ['failed', 'bounced']))
                        <button wire:click="hookCustom('resend', {'id': {{ $item->id }}})"
                                wire:confirm="이 이메일을 재발송하시겠습니까?"
                                wire:loading.attr="disabled"
                                wire:target="hookCustom"
                                class="text-yellow-600 hover:text-yellow-900 disabled:opacity-50 disabled:cursor-not-allowed relative"
                                title="재발송">
                            <svg wire:loading.remove wire:target="hookCustom" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                            </svg>
                            <svg wire:loading wire:target="hookCustom" class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </button>
                        @endif
                        
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
                <td colspan="9" class="px-3 py-4 text-center text-xs text-gray-500">
                    이메일 로그가 없습니다.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>