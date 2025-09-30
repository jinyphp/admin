{{--
    이메일 발송 로그 생성 폼
    
    @package jiny/admin
    @subpackage admin_email_logs
    @description 새로운 이메일을 작성하고 발송하는 폼입니다.
                템플릿 선택, 받는사람/보내는사람 정보, 제목, 본문 등을 입력받습니다.
    @version 1.0
--}}

{{-- Email 발송 폼 --}}
<div class="space-y-4">
    {{-- 템플릿 선택 --}}
    @if(isset($jsonData['create']['enableTemplateSelector']) && $jsonData['create']['enableTemplateSelector'])
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">템플릿 선택 (선택사항)</label>
        <select wire:model="form.template_id" 
                wire:change="hookCustom('loadTemplate', {'template_id': $wire.form.template_id})"
                class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            <option value="">직접 작성</option>
            @php
                $templateList = $jsonData['templates'] ?? $templates ?? [];
            @endphp
            @foreach($templateList as $template)
                <option value="{{ $template->id }}">{{ $template->name }}</option>
            @endforeach
        </select>
        @error('form.template_id')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
        
        @if(empty($templateList) || count($templateList) == 0)
            <p class="mt-1 text-xs text-gray-500">* 등록된 템플릿이 없습니다. <a href="{{ route('admin.system.mail.templates.create') }}" class="text-blue-600 hover:text-blue-800">템플릿 생성하기</a></p>
        @endif
    </div>
    @endif

    {{-- 받는 사람 정보 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">
                받는 사람 이메일 <span class="text-red-500">*</span>
            </label>
            <input type="email" 
                   wire:model="form.to_email"
                   placeholder="example@email.com"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   required>
            @error('form.to_email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">받는 사람 이름</label>
            <input type="text" 
                   wire:model="form.to_name"
                   placeholder="홍길동"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.to_name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- 보내는 사람 정보 --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">보내는 사람 이메일</label>
            <input type="email" 
                   wire:model="form.from_email"
                   placeholder="{{ config('mail.from.address', 'noreply@example.com') }}"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.from_email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label class="block text-xs font-medium text-gray-700 mb-1">보내는 사람 이름</label>
            <input type="text" 
                   wire:model="form.from_name"
                   placeholder="{{ config('mail.from.name', '회사명') }}"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
            @error('form.from_name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- 제목 --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            제목 <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               wire:model="form.subject"
               placeholder="이메일 제목을 입력하세요"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               required>
        @error('form.subject')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 내용 --}}
    <div>
        <label class="block text-xs font-medium text-gray-700 mb-1">
            내용 <span class="text-red-500">*</span>
        </label>
        <div wire:ignore>
            <textarea wire:model="form.body"
                      rows="10"
                      placeholder="이메일 내용을 작성하세요&#10;&#10;변수 사용 예시:&#10;[user_name] - 받는 사람 이름&#10;[company_name] - 회사명&#10;[date] - 현재 날짜"
                      class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      required></textarea>
        </div>
        @error('form.body')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 추가 설정 (선택사항) --}}
    <div class="pt-4 border-t border-gray-200">
        <h3 class="text-xs font-medium text-gray-700 mb-3">추가 설정 (선택사항)</h3>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            {{-- CC --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">참조 (CC)</label>
                <input type="text" 
                       wire:model="form.cc"
                       placeholder="cc@example.com, cc2@example.com"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">여러 개는 쉼표로 구분</p>
                @error('form.cc')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- BCC --}}
            <div>
                <label class="block text-xs font-medium text-gray-700 mb-1">숨은 참조 (BCC)</label>
                <input type="text" 
                       wire:model="form.bcc"
                       placeholder="bcc@example.com, bcc2@example.com"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                <p class="mt-1 text-xs text-gray-500">여러 개는 쉼표로 구분</p>
                @error('form.bcc')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>

    {{-- 상태 (숨김 - 자동으로 pending 설정) --}}
    <input type="hidden" wire:model="form.status" value="pending">

    {{-- 템플릿 변수 도움말 --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-3">
        <h4 class="text-xs font-medium text-blue-800 mb-2">템플릿 변수 사용 가이드</h4>
        <ul class="text-xs text-blue-700 space-y-1">
            <li>• <strong>[user_name]</strong> - 받는 사람 이름</li>
            <li>• <strong>[email]</strong> - 받는 사람 이메일</li>
            <li>• <strong>[company_name]</strong> - 회사명</li>
            <li>• <strong>[date]</strong> - 현재 날짜</li>
            <li>• <strong>[url]</strong> - 링크 URL</li>
        </ul>
    </div>
</div>

@push('scripts')
<script>
    // 템플릿 로드 시 폼 업데이트
    window.addEventListener('templateLoaded', event => {
        if (event.detail && event.detail.data) {
            // 템플릿 데이터로 폼 필드 업데이트
            console.log('Template loaded:', event.detail.data);
        }
    });
</script>
@endpush