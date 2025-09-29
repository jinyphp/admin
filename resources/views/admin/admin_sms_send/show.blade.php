{{--
    SMS 발송 상세보기 컨텐츠
    admin-show 컴포넌트에 포함될 콘텐츠만 포함
--}}
<div class="bg-white border border-gray-200 rounded-lg">
    <div class="p-6">
        {{-- 발송 상태 표시 --}}
        <div class="mb-6 p-4 bg-gray-50 rounded-lg">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-sm font-medium text-gray-900">발송 상태</h3>
                    <div class="mt-2">
                        @if($data->status === 'sent')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                발송완료
                            </span>
                        @elseif($data->status === 'delivered')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                                수신확인
                            </span>
                        @elseif($data->status === 'failed')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                                </svg>
                                발송실패
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/>
                                </svg>
                                대기중
                            </span>
                        @endif
                    </div>
                </div>
                @if(isset($data->sent_at) && $data->sent_at)
                <div class="text-right">
                    <p class="text-xs text-gray-500">발송시간</p>
                    <p class="text-sm font-medium text-gray-900">{{ \Carbon\Carbon::parse($data->sent_at)->format('Y-m-d H:i:s') }}</p>
                </div>
                @endif
            </div>
        </div>

        {{-- 상세 정보 --}}
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-4 gap-y-4">
            <div>
                <dt class="text-xs font-medium text-gray-500">ID</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ $data->id }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500">수신번호</dt>
                <dd class="mt-1 text-sm text-gray-900 font-medium">{{ $data->to_number }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500">발신번호</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ isset($data->from_number) ? $data->from_number : '기본 발신번호' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500">제공업체</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ isset($data->provider_name) ? $data->provider_name : '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500">메시지 길이</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ isset($data->message_length) ? $data->message_length : mb_strlen($data->message) }} 자</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500">메시지 ID</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ isset($data->message_id) ? $data->message_id : '-' }}</dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500">발송 비용</dt>
                <dd class="mt-1 text-sm text-gray-900">
                    @if(isset($data->cost) && $data->cost)
                        ${{ number_format($data->cost, 4) }} {{ isset($data->currency) ? $data->currency : 'USD' }}
                    @else
                        -
                    @endif
                </dd>
            </div>

            <div>
                <dt class="text-xs font-medium text-gray-500">등록일시</dt>
                <dd class="mt-1 text-sm text-gray-900">{{ \Carbon\Carbon::parse($data->created_at)->format('Y-m-d H:i:s') }}</dd>
            </div>
        </dl>

        {{-- 메시지 내용 --}}
        <div class="mt-6">
            <dt class="text-xs font-medium text-gray-500">메시지 내용</dt>
            <dd class="mt-2 p-3 bg-gray-50 rounded-lg">
                <pre class="text-sm text-gray-900 whitespace-pre-wrap">{{ $data->message }}</pre>
            </dd>
        </div>

        {{-- 오류 메시지 (실패한 경우) --}}
        @if($data->status === 'failed' && isset($data->error_message) && $data->error_message)
        <div class="mt-6 p-3 bg-red-50 border border-red-200 rounded-lg">
            <dt class="text-xs font-medium text-red-800 mb-1">오류 메시지</dt>
            <dd class="text-sm text-red-700">{{ $data->error_message }}</dd>
            @if(isset($data->error_code) && $data->error_code)
                <dd class="text-xs text-red-600 mt-1">오류 코드: {{ $data->error_code }}</dd>
            @endif
        </div>
        @endif

        {{-- 응답 데이터 (개발자용) --}}
        @if(isset($data->response_data) && $data->response_data)
        <div class="mt-6">
            <details class="group">
                <summary class="cursor-pointer text-xs font-medium text-gray-500 hover:text-gray-700">
                    <span class="group-open:hidden">▶</span>
                    <span class="hidden group-open:inline">▼</span>
                    응답 데이터 (개발자용)
                </summary>
                <div class="mt-2 p-3 bg-gray-50 rounded-lg">
                    <pre class="text-xs text-gray-600">{{ json_encode(json_decode($data->response_data), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                </div>
            </details>
        </div>
        @endif
    </div>

    {{-- SMS 발송/재발송 버튼 영역 --}}
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex items-center justify-between">
        <div class="flex items-center space-x-2">
            @if($data->status === 'pending')
                <button onclick="sendSms({{ $data->id }})"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                    </svg>
                    SMS 발송
                </button>
            @elseif($data->status === 'failed')
                <button onclick="resendSms({{ $data->id }})"
                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-md text-white bg-orange-600 hover:bg-orange-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                    </svg>
                    재발송
                </button>
            @endif
        </div>


    </div>
</div>

{{-- JavaScript for SMS sending --}}
<script>
function sendSms(id) {
    if (confirm('이 SMS를 발송하시겠습니까?')) {
        fetch(`/admin/sms/send/${id}/send`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('발송 실패: ' + data.message);
            }
        })
        .catch(error => {
            alert('오류가 발생했습니다: ' + error);
        });
    }
}

function resendSms(id) {
    if (confirm('이 SMS를 재발송하시겠습니까?')) {
        fetch(`/admin/sms/send/${id}/resend`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                location.reload();
            } else {
                alert('재발송 실패: ' + data.message);
            }
        })
        .catch(error => {
            alert('오류가 발생했습니다: ' + error);
        });
    }
}
</script>
