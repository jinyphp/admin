{{--
    Webhooklogs 테이블 뷰
    Tailwind CSS 스타일 적용 및 Livewire 기능 통합
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
                
                {{-- 동적 컬럼들 --}}
                {{-- 실제 사용시 필요한 컬럼을 추가하세요 --}}
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    Name
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    Status
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    Created
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
                
                {{-- 동적 데이터 --}}
                {{-- 실제 사용시 필요한 데이터를 표시하세요 --}}
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <a href="{{ route('admin.system.webhook.logs.show', $item->id) }}"
                       class="text-xs text-blue-600 hover:text-blue-900 font-medium">
                        {{ $item->name ?? '-' }}
                    </a>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if(isset($item->status) && $item->status)
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                            Active
                        </span>
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-600">
                            Inactive
                        </span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500">
                    @if(isset($item->created_at) && $item->created_at)
                        {{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}
                    @else
                        -
                    @endif
                </td>
                
                {{-- 액션 버튼들 --}}
                <td class="px-3 py-2.5 whitespace-nowrap text-right text-xs font-medium">
                    <div class="flex items-center space-x-1">
                        {{-- 보기 --}}
                        <a href="{{ route('admin.system.webhook.logs.show', $item->id) }}"
                           class="text-gray-600 hover:text-gray-900"
                           title="View">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        {{-- 수정 버튼 제거 - 로그는 수정 불가 --}}
                        {{-- 삭제 --}}
                        <button wire:click="requestDeleteSingle({{ $item->id }})"
                                class="text-red-600 hover:text-red-900"
                                title="Delete">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="px-3 py-4 text-center text-xs text-gray-500">
                    No webhooklogs found.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>