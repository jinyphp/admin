<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                {{-- 체크박스 --}}
                <th class="px-4 py-3 text-left">
                    <input type="checkbox" 
                           wire:model.live="selectAll"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </th>
                
                {{-- ID --}}
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <button wire:click="sortBy('id')" class="flex items-center space-x-1">
                        <span>ID</span>
                        @if($sortField === 'id')
                            @if($sortDirection === 'asc')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 10l5-5 5 5H5z"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M15 10l-5 5-5-5h10z"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                
                {{-- IP 정보 --}}
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    <button wire:click="sortBy('ip_address')" class="flex items-center space-x-1">
                        <span>IP 정보</span>
                        @if($sortField === 'ip_address')
                            @if($sortDirection === 'asc')
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M5 10l5-5 5 5H5z"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M15 10l-5 5-5-5h10z"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                
                {{-- 타입 --}}
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    타입
                </th>
                
                {{-- 상태 --}}
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    상태
                </th>
                
                {{-- 접근 횟수 --}}
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    접근 횟수
                </th>
                
                {{-- 접근/만료 정보 --}}
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    접근/만료 정보
                </th>
                
                {{-- 액션 --}}
                <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                    액션
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($rows as $row)
            <tr class="hover:bg-gray-50">
                {{-- 체크박스 --}}
                <td class="px-4 py-3 whitespace-nowrap">
                    <input type="checkbox" 
                           wire:model.live="selected"
                           value="{{ $row->id }}"
                           class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                </td>
                
                {{-- ID --}}
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    {{ $row->id }}
                </td>
                
                {{-- IP 정보 --}}
                <td class="px-4 py-3 text-sm">
                    <div class="space-y-1">
                        <div class="font-medium text-gray-900">
                            @if($row->type === 'range')
                                {{ $row->ip_range_start }} ~ {{ $row->ip_range_end }}
                            @elseif($row->type === 'cidr')
                                {{ $row->ip_address }}/{{ $row->cidr_prefix }}
                            @else
                                {{ $row->ip_address }}
                            @endif
                        </div>
                        <div class="text-xs text-gray-500">
                            {{ $row->description }}
                        </div>
                    </div>
                </td>
                
                {{-- 타입 --}}
                <td class="px-4 py-3 whitespace-nowrap">
                    @if($row->type === 'single')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                            단일 IP
                        </span>
                    @elseif($row->type === 'range')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                            IP 범위
                        </span>
                    @elseif($row->type === 'cidr')
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                            CIDR
                        </span>
                    @endif
                </td>
                
                {{-- 상태 --}}
                <td class="px-4 py-3 whitespace-nowrap">
                    @if($row->expires_at && \Carbon\Carbon::parse($row->expires_at)->isPast())
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                            만료됨
                        </span>
                    @elseif($row->is_active)
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                            활성
                        </span>
                    @else
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                            비활성
                        </span>
                    @endif
                </td>
                
                {{-- 접근 횟수 --}}
                <td class="px-4 py-3 whitespace-nowrap text-sm text-gray-900">
                    {{ $row->access_count ?? 0 }}회
                </td>
                
                {{-- 접근/만료 정보 --}}
                <td class="px-4 py-3 text-sm text-gray-500">
                    <div class="space-y-1">
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-xs">
                                @if($row->last_accessed_at)
                                    {{ \Carbon\Carbon::parse($row->last_accessed_at)->diffForHumans() }}
                                @else
                                    접근 기록 없음
                                @endif
                            </span>
                        </div>
                        <div class="flex items-center space-x-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-xs {{ $row->expires_at && \Carbon\Carbon::parse($row->expires_at)->isPast() ? 'text-red-600 font-semibold' : '' }}">
                                @if($row->expires_at)
                                    @if(\Carbon\Carbon::parse($row->expires_at)->isPast())
                                        만료됨
                                    @else
                                        {{ \Carbon\Carbon::parse($row->expires_at)->format('Y-m-d H:i') }}
                                    @endif
                                @else
                                    무제한
                                @endif
                            </span>
                        </div>
                    </div>
                </td>
                
                {{-- 액션 --}}
                <td class="px-4 py-3 whitespace-nowrap text-right text-sm font-medium">
                    <div class="flex justify-end space-x-2">
                        {{-- 상태 토글 버튼 --}}
                        <button wire:click="toggleStatus({{ $row->id }})"
                                class="text-indigo-600 hover:text-indigo-900"
                                title="{{ $row->is_active ? '비활성화' : '활성화' }}">
                            @if($row->is_active)
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path>
                                </svg>
                            @else
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                            @endif
                        </button>
                        
                        {{-- 보기 버튼 --}}
                        <a href="{{ route('admin.security.ip-whitelist.show', $row->id) }}"
                           class="text-blue-600 hover:text-blue-900"
                           title="상세보기">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        
                        {{-- 수정 버튼 --}}
                        <a href="{{ route('admin.security.ip-whitelist.edit', $row->id) }}"
                           class="text-green-600 hover:text-green-900"
                           title="수정">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        
                        {{-- 삭제 버튼 --}}
                        <button wire:click="$dispatch('delete-single', { id: {{ $row->id }} })"
                                class="text-red-600 hover:text-red-900"
                                title="삭제">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="px-4 py-8 text-center text-gray-500">
                    데이터가 없습니다.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>

{{-- 페이지네이션 --}}
@if($rows->hasPages())
<div class="mt-4">
    {{ $rows->links() }}
</div>
@endif