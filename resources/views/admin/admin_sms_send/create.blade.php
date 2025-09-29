<div>
    <div class="mb-4">
        <label for="provider_id" class="block text-xs font-medium text-gray-700 mb-1">SMS 제공업체</label>
        <select wire:model="form.provider_id" 
                class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                id="provider_id" 
                name="provider_id">
            <option value="">기본 제공업체 사용</option>
            @php
                $providers = \Jiny\Admin\Models\AdminSmsProvider::where('is_active', true)
                    ->orderBy('priority', 'desc')
                    ->get();
            @endphp
            @foreach($providers as $provider)
                <option value="{{ $provider->id }}">
                    {{ $provider->provider_name }} ({{ ucfirst($provider->driver_type ?? 'vonage') }})
                    @if($provider->is_default) - 기본 @endif
                </option>
            @endforeach
        </select>
    </div>

    <div class="mb-4">
        <label for="to_number" class="block text-xs font-medium text-gray-700 mb-1">수신번호 *</label>
        <input type="text" 
               wire:model="form.to_number"
               class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
               id="to_number" 
               name="to_number" 
               placeholder="예: 010-1234-5678 또는 821012345678"
               required>
        <p class="mt-1 text-xs text-gray-500">한국 번호는 010으로 시작하거나 82 국가코드를 포함할 수 있습니다</p>
    </div>

    <div class="mb-4">
        <label for="from_number" class="block text-xs font-medium text-gray-700 mb-1">발신번호 (선택)</label>
        <input type="text" 
               wire:model="form.from_number"
               class="h-8 px-2.5 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
               id="from_number" 
               name="from_number" 
               placeholder="비워두면 제공업체 기본 발신번호 사용">
    </div>

    <div class="mb-4">
        <label for="message" class="block text-xs font-medium text-gray-700 mb-1">메시지 내용 *</label>
        <textarea wire:model="form.message"
                  wire:model.lazy="form.message"
                  class="px-2.5 py-2 text-xs border border-gray-200 rounded-md w-full focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500" 
                  id="message" 
                  name="message" 
                  rows="5"
                  placeholder="발송할 메시지를 입력하세요"
                  required></textarea>
        <div class="mt-1 flex justify-between text-xs text-gray-500">
            <span>글자 수: <span wire:ignore id="char-count">0</span> 자</span>
            <span>예상 건수: <span wire:ignore id="msg-count">1</span> 건</span>
        </div>
    </div>

    <div class="bg-yellow-50 p-3 rounded-md mb-4">
        <h6 class="text-xs font-semibold text-yellow-900 mb-2">⚠️ 발송 전 확인사항</h6>
        <ul class="text-xs text-yellow-700 space-y-1">
            <li>• SMS 발송은 실제 비용이 발생합니다.</li>
            <li>• 한글 70자, 영문 160자를 초과하면 장문(LMS)으로 발송됩니다.</li>
            <li>• 발송 후 취소가 불가능합니다.</li>
            <li>• 수신자 동의 없는 광고성 메시지 발송은 법적 제재를 받을 수 있습니다.</li>
        </ul>
    </div>

    <div class="flex gap-2">
        <button type="button" 
                wire:click="callCustomAction('sendSms')"
                wire:confirm="SMS를 발송하시겠습니까?"
                class="px-4 py-2 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
            SMS 발송
        </button>
        <button type="button" 
                wire:click="callCustomAction('testSend')"
                wire:confirm="관리자 번호로 테스트 발송하시겠습니까?"
                class="px-4 py-2 bg-gray-600 text-white text-xs rounded-md hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500">
            테스트 발송 (관리자 번호로)
        </button>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const messageInput = document.getElementById('message');
    const charCount = document.getElementById('char-count');
    const msgCount = document.getElementById('msg-count');
    
    function updateCount() {
        const length = messageInput.value.length;
        charCount.textContent = length;
        
        // 메시지 건수 계산 (한글 기준)
        let count = 1;
        if (length > 70) {
            count = Math.ceil(length / 67);
        }
        msgCount.textContent = count;
    }
    
    messageInput.addEventListener('input', updateCount);
    updateCount();
});
</script>