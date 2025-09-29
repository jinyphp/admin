{{-- IP 화이트리스트 상세 보기 --}}
<div class="space-y-6">
    {{-- 기본 정보 --}}
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">IP 화이트리스트 정보</h3>
        </div>
        <div class="border-t border-gray-200">
            <dl>
                {{-- ID --}}
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">ID</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $data->id }}</dd>
                </div>

                {{-- IP 타입 --}}
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">IP 타입</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        @if($data->type === 'single')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                단일 IP
                            </span>
                        @elseif($data->type === 'range')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                IP 범위
                            </span>
                        @elseif($data->type === 'cidr')
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-purple-100 text-purple-800">
                                CIDR
                            </span>
                        @endif
                    </dd>
                </div>

                {{-- IP 주소 --}}
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">IP 주소</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        <code class="font-mono bg-gray-100 px-2 py-1 rounded">
                            {{ $data->ip_display ?? $data->ip_address }}
                        </code>
                        @if(isset($data->ip_count) && $data->ip_count > 1)
                            <span class="ml-2 text-xs text-gray-500">({{ number_format($data->ip_count) }}개 IP)</span>
                        @endif
                    </dd>
                </div>

                {{-- 설명 --}}
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">설명</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $data->description }}</dd>
                </div>

                {{-- 상태 --}}
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">상태</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        @if(isset($data->status_class))
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $data->status_class === 'success' ? 'bg-green-100 text-green-800' : 
                                   ($data->status_class === 'danger' ? 'bg-red-100 text-red-800' : 'bg-gray-100 text-gray-800') }}">
                                {{ $data->status }}
                            </span>
                        @else
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                {{ $data->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                                {{ $data->is_active ? '활성' : '비활성' }}
                            </span>
                        @endif
                    </dd>
                </div>

                {{-- 추가한 관리자 --}}
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">추가한 관리자</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ $data->added_by ?? '-' }}</dd>
                </div>

                {{-- 접근 횟수 --}}
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">총 접근 횟수</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">{{ number_format($data->access_count ?? 0) }}회</dd>
                </div>

                {{-- 마지막 접근 --}}
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">마지막 접근</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        @if($data->last_accessed_at)
                            {{ \Carbon\Carbon::parse($data->last_accessed_at)->format('Y-m-d H:i:s') }}
                            <span class="text-xs text-gray-500">({{ \Carbon\Carbon::parse($data->last_accessed_at)->diffForHumans() }})</span>
                        @else
                            -
                        @endif
                    </dd>
                </div>

                {{-- 만료일시 --}}
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">만료일시</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        @if($data->expires_at)
                            {{ \Carbon\Carbon::parse($data->expires_at)->format('Y-m-d H:i:s') }}
                            @if(\Carbon\Carbon::parse($data->expires_at)->isPast())
                                <span class="ml-2 text-xs text-red-600">(만료됨)</span>
                            @else
                                <span class="ml-2 text-xs text-gray-500">({{ \Carbon\Carbon::parse($data->expires_at)->diffForHumans() }})</span>
                            @endif
                        @else
                            무제한
                        @endif
                    </dd>
                </div>

                {{-- 생성일시 --}}
                <div class="bg-white px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">생성일시</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($data->created_at)->format('Y-m-d H:i:s') }}
                    </dd>
                </div>

                {{-- 수정일시 --}}
                <div class="bg-gray-50 px-4 py-5 sm:grid sm:grid-cols-3 sm:gap-4 sm:px-6">
                    <dt class="text-sm font-medium text-gray-500">수정일시</dt>
                    <dd class="mt-1 text-sm text-gray-900 sm:mt-0 sm:col-span-2">
                        {{ \Carbon\Carbon::parse($data->updated_at)->format('Y-m-d H:i:s') }}
                    </dd>
                </div>
            </dl>
        </div>
    </div>

    {{-- 접근 통계 --}}
    @if(isset($accessStats))
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">접근 통계</h3>
        </div>
        <div class="border-t border-gray-200 px-4 py-5 sm:px-6">
            <div class="grid grid-cols-3 gap-4">
                <div class="text-center">
                    <div class="text-2xl font-bold text-gray-900">{{ number_format($accessStats->total_access ?? 0) }}</div>
                    <div class="text-sm text-gray-500">전체 접근</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-green-600">{{ number_format($accessStats->allowed_count ?? 0) }}</div>
                    <div class="text-sm text-gray-500">허용됨</div>
                </div>
                <div class="text-center">
                    <div class="text-2xl font-bold text-red-600">{{ number_format($accessStats->blocked_count ?? 0) }}</div>
                    <div class="text-sm text-gray-500">차단됨</div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- 최근 접근 로그 --}}
    @if(isset($recentAccessLogs) && count($recentAccessLogs) > 0)
    <div class="bg-white shadow overflow-hidden sm:rounded-lg">
        <div class="px-4 py-5 sm:px-6">
            <h3 class="text-lg leading-6 font-medium text-gray-900">최근 접근 로그</h3>
        </div>
        <div class="border-t border-gray-200">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">시간</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">URL</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">사용자</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">상태</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($recentAccessLogs as $log)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ \Carbon\Carbon::parse($log->created_at)->format('m-d H:i') }}
                        </td>
                        <td class="px-6 py-4 text-sm text-gray-900">
                            <span class="text-xs text-gray-500">{{ $log->method }}</span>
                            {{ Str::limit($log->url, 50) }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                            {{ $log->email ?? 'Guest' }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($log->is_allowed)
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                                    허용
                                </span>
                            @else
                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">
                                    차단
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>