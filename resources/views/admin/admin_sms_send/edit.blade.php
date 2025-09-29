{{--
    SMS 발송 수정 폼
    SMS 메시지 정보를 수정하는 폼입니다.
--}}
<div>
    <h2 class="text-lg font-semibold text-gray-900 mb-4">SMS 발송 정보 수정</h2>

        {{-- 수신번호 --}}
        <div class="mb-4">
            <label for="to_number" class="block text-xs font-medium text-gray-700 mb-1">
                수신번호 <span class="text-red-500">*</span>
            </label>
            <input type="text"
                   wire:model.defer="form.to_number"
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   id="to_number"
                   name="to_number"
                   placeholder="010-0000-0000"
                   required>
        </div>

        {{-- 발신번호 --}}
        <div class="mb-4">
            <label for="from_number" class="block text-xs font-medium text-gray-700 mb-1">
                발신번호
            </label>
            <input type="text"
                   wire:model.defer="form.from_number"
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   id="from_number"
                   name="from_number"
                   placeholder="기본 발신번호 사용">
        </div>

        {{-- 제공업체 선택 --}}
        <div class="mb-4">
            <label for="provider_id" class="block text-xs font-medium text-gray-700 mb-1">
                SMS 제공업체
            </label>
            <select wire:model.defer="form.provider_id"
                    class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    id="provider_id"
                    name="provider_id">
                <option value="">-- 제공업체 선택 --</option>
                @if(isset($providers))
                    @foreach($providers as $provider)
                        <option value="{{ $provider->id }}">
                            {{ $provider->provider_name }}
                        </option>
                    @endforeach
                @endif
            </select>
        </div>

        {{-- 메시지 내용 --}}
        <div class="mb-4">
            <label for="message" class="block text-xs font-medium text-gray-700 mb-1">
                메시지 내용 <span class="text-red-500">*</span>
            </label>
            <textarea wire:model.defer="form.message"
                      class="px-2.5 py-2 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      id="message"
                      name="message"
                      rows="5"
                      placeholder="SMS 메시지 내용을 입력하세요"
                      required></textarea>
            <div class="mt-1 text-xs text-gray-500">
                <span id="message-length">0</span> / 90 bytes (SMS), 2000 bytes (LMS)
            </div>
        </div>

        {{-- 발송 상태 --}}
        <div class="mb-4">
            <label for="status" class="block text-xs font-medium text-gray-700 mb-1">
                발송 상태
            </label>
            <select wire:model.defer="form.status"
                    class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                    id="status"
                    name="status">
                <option value="pending">대기중</option>
                <option value="sent">발송완료</option>
                <option value="delivered">수신확인</option>
                <option value="failed">발송실패</option>
            </select>
        </div>

        {{-- 예약 발송 --}}
        <div class="mb-4">
            <label class="flex items-center">
                @php
                    $isChecked = isset($form['scheduled_at']) && $form['scheduled_at'];
                @endphp
                <input type="checkbox"
                       wire:model.defer="form.is_scheduled"
                       class="h-4 w-4 text-blue-600 border-gray-200 rounded focus:ring-1 focus:ring-blue-500"
                       id="is_scheduled"
                       name="is_scheduled"
                       @if($isChecked) checked @endif>
                <span class="ml-2 text-xs text-gray-700">예약 발송</span>
            </label>
        </div>

        @php
            $isScheduled = isset($form['scheduled_at']) && $form['scheduled_at'];
        @endphp
        <div class="mb-4" id="scheduled_at_container" style="display: {{ $isScheduled ? 'block' : 'none' }}">
            <label for="scheduled_at" class="block text-xs font-medium text-gray-700 mb-1">
                예약 발송 시간
            </label>
            <input type="datetime-local"
                   wire:model.defer="form.scheduled_at"
                   class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   id="scheduled_at"
                   name="scheduled_at">
        </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // 메시지 길이 계산
    const messageField = document.getElementById('message');
    const messageLengthSpan = document.getElementById('message-length');
    
    function updateMessageLength() {
        if (messageField && messageLengthSpan) {
            const length = new Blob([messageField.value]).size;
            messageLengthSpan.textContent = length;
        }
    }
    
    // 초기 길이 표시
    updateMessageLength();
    
    // 입력 시 길이 업데이트
    if (messageField) {
        messageField.addEventListener('input', updateMessageLength);
    }
    
    // 예약 발송 토글
    const isScheduledCheckbox = document.getElementById('is_scheduled');
    const scheduledAtContainer = document.getElementById('scheduled_at_container');
    
    if (isScheduledCheckbox && scheduledAtContainer) {
        isScheduledCheckbox.addEventListener('change', function() {
            scheduledAtContainer.style.display = this.checked ? 'block' : 'none';
        });
    }
});
</script>
