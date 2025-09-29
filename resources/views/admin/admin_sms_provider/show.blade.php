{{-- 
    SMS Provider 상세 보기 뷰
    Tailwind CSS를 사용한 깔끔한 레이아웃
--}}
<div class="p-6">
    <h2 class="text-lg font-semibold text-gray-900 mb-6">SMS 제공업체 상세 정보</h2>
    
    <div class="bg-white border border-gray-200 rounded-lg shadow-sm">
        <div class="p-6 space-y-6">
            {{-- 기본 정보 섹션 --}}
            <div>
                <h3 class="text-sm font-medium text-gray-900 mb-4">기본 정보</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">제공업체명</dt>
                        <dd class="text-sm text-gray-900">{{ $data->provider_name ?? '-' }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">제공업체 타입</dt>
                        <dd class="text-sm text-gray-900">{{ $data->provider_type ?? '-' }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">발신번호</dt>
                        <dd class="text-sm text-gray-900">{{ $data->from_number ?? '-' }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">발신자명</dt>
                        <dd class="text-sm text-gray-900">{{ $data->from_name ?? '-' }}</dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">상태</dt>
                        <dd>
                            @if($data->is_active ?? false)
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                    활성
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                    비활성
                                </span>
                            @endif
                            @if($data->is_default ?? false)
                                <span class="ml-2 inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                    기본
                                </span>
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">우선순위</dt>
                        <dd class="text-sm text-gray-900">{{ $data->priority ?? 0 }}</dd>
                    </div>
                </dl>
            </div>
            
            {{-- API 정보 섹션 --}}
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">API 정보</h3>
                <dl class="grid grid-cols-1 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">API Key</dt>
                        <dd class="text-sm text-gray-900 font-mono">
                            @if(!empty($data->api_key))
                                {{ substr($data->api_key, 0, 8) }}****{{ substr($data->api_key, -4) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">API Secret</dt>
                        <dd class="text-sm text-gray-900 font-mono">
                            @if(!empty($data->api_secret))
                                ****{{ substr($data->api_secret, -4) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </dd>
                    </div>
                    
                    @if(!empty($data->webhook_url))
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">Webhook URL</dt>
                        <dd class="text-sm text-gray-900 font-mono">{{ $data->webhook_url }}</dd>
                    </div>
                    @endif
                </dl>
            </div>
            
            {{-- 통계 정보 섹션 --}}
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">사용 통계</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">발송 건수</dt>
                        <dd class="text-sm text-gray-900">{{ number_format($data->sent_count ?? 0) }}건</dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">실패 건수</dt>
                        <dd class="text-sm text-gray-900">{{ number_format($data->failed_count ?? 0) }}건</dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">잔액</dt>
                        <dd class="text-sm text-gray-900">
                            @if($data->balance !== null)
                                ${{ number_format($data->balance, 2) }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">마지막 사용</dt>
                        <dd class="text-sm text-gray-900">
                            @if($data->last_used_at)
                                {{ \Carbon\Carbon::parse($data->last_used_at)->format('Y-m-d H:i:s') }}
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
            
            {{-- 설명 섹션 --}}
            @if(!empty($data->description))
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">설명</h3>
                <p class="text-sm text-gray-700">{{ $data->description }}</p>
            </div>
            @endif
            
            {{-- 추가 설정 섹션 --}}
            @if(!empty($data->settings))
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-medium text-gray-900 mb-4">추가 설정</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <pre class="text-xs text-gray-700 whitespace-pre-wrap">{{ json_encode(json_decode($data->settings), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </div>
            @endif
            
            {{-- 타임스탬프 섹션 --}}
            <div class="border-t border-gray-200 pt-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">생성일</dt>
                        <dd class="text-sm text-gray-900">
                            @if($data->created_at)
                                {{ \Carbon\Carbon::parse($data->created_at)->format('Y-m-d H:i:s') }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                    
                    <div>
                        <dt class="text-xs font-medium text-gray-500 mb-1">수정일</dt>
                        <dd class="text-sm text-gray-900">
                            @if($data->updated_at)
                                {{ \Carbon\Carbon::parse($data->updated_at)->format('Y-m-d H:i:s') }}
                            @else
                                -
                            @endif
                        </dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>