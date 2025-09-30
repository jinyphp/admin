{{-- 패스워드 히스토리 테이블 --}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    사용자
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    변경일시
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    만료일시
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    상태
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    유형
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    사용횟수
                </th>
                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                    변경사유
                </th>
                <th scope="col" class="relative px-6 py-3">
                    <span class="sr-only">작업</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($rows ?? [] as $row)
                <tr>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if(isset($row->user))
                            <div class="flex items-center">
                                <div>
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $row->user->name ?? 'Unknown' }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $row->user->email ?? '' }}
                                    </div>
                                </div>
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="text-sm text-gray-900">
                            {{ $row->changed_at ? \Carbon\Carbon::parse($row->changed_at)->format('Y-m-d H:i') : '-' }}
                        </div>
                        @if($row->changed_at)
                            <div class="text-xs text-gray-500">
                                {{ \Carbon\Carbon::parse($row->changed_at)->diffForHumans() }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($row->expires_at)
                            <div class="text-sm text-gray-900">
                                {{ \Carbon\Carbon::parse($row->expires_at)->format('Y-m-d H:i') }}
                            </div>
                            @php
                                $isExpired = \Carbon\Carbon::parse($row->expires_at)->isPast();
                                $daysRemaining = now()->diffInDays(\Carbon\Carbon::parse($row->expires_at), false);
                            @endphp
                            <div class="text-xs {{ $isExpired ? 'text-red-600' : 'text-gray-500' }}">
                                @if($isExpired)
                                    {{ abs($daysRemaining) }}일 전 만료
                                @else
                                    {{ $daysRemaining }}일 후 만료
                                @endif
                            </div>
                        @else
                            <span class="text-sm text-gray-400">-</span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($row->is_expired)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                만료
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                활성
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if($row->is_temporary)
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">
                                임시
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-gray-100 text-gray-800">
                                일반
                            </span>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                        {{ $row->usage_count ?? 0 }}회
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                        {{ Str::limit($row->change_reason ?? '-', 30) }}
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <a href="{{ route('admin.system.user.password.show', $row->id) }}" 
                           class="text-indigo-600 hover:text-indigo-900">
                            상세보기
                        </a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-sm text-gray-500">
                        패스워드 히스토리가 없습니다.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>