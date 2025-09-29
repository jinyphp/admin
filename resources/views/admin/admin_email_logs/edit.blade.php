{{--
    이메일 발송 로그 수정 폼
    
    @package jiny/admin
    @subpackage admin_email_logs
    @description 기존 이메일 로그를 수정하는 폼입니다.
                pending 상태의 이메일만 수정 가능합니다.
    @version 1.0
--}}

{{-- EmailLogs 수정 폼 --}}
<div class="space-y-4">
    {{-- 상태 표시 (읽기 전용) --}}
    @if(isset($form['status']))
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-3">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-yellow-600 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
            </svg>
            <div>
                <p class="text-sm font-medium text-yellow-800">현재 상태: 
                    @if($form['status'] == 'pending')
                        <span class="text-yellow-600">대기중</span> - 수정 가능
                    @elseif($form['status'] == 'sent')
                        <span class="text-green-600">발송완료</span> - 수정 불가
                    @elseif($form['status'] == 'failed')
                        <span class="text-red-600">실패</span> - 수정 불가
                    @else
                        <span>{{ $form['status'] }}</span>
                    @endif
                </p>
            </div>
        </div>
    </div>
    @endif

    {{-- 받는 사람 정보 --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="to_email" class="block text-xs font-medium text-gray-700 mb-1">
                받는 사람 이메일 <span class="text-red-500">*</span>
            </label>
            <input type="email" 
                   wire:model="form.to_email" 
                   id="to_email" 
                   name="to_email"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="example@email.com"
                   @if(isset($form['status']) && $form['status'] != 'pending') disabled @endif
                   required>
            @error('form.to_email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="to_name" class="block text-xs font-medium text-gray-700 mb-1">
                받는 사람 이름
            </label>
            <input type="text" 
                   wire:model="form.to_name" 
                   id="to_name" 
                   name="to_name"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="홍길동"
                   @if(isset($form['status']) && $form['status'] != 'pending') disabled @endif>
            @error('form.to_name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- 보내는 사람 정보 --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <label for="from_email" class="block text-xs font-medium text-gray-700 mb-1">
                보내는 사람 이메일
            </label>
            <input type="email" 
                   wire:model="form.from_email" 
                   id="from_email" 
                   name="from_email"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="noreply@company.com"
                   @if(isset($form['status']) && $form['status'] != 'pending') disabled @endif>
            @error('form.from_email')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <label for="from_name" class="block text-xs font-medium text-gray-700 mb-1">
                보내는 사람 이름
            </label>
            <input type="text" 
                   wire:model="form.from_name" 
                   id="from_name" 
                   name="from_name"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="회사명"
                   @if(isset($form['status']) && $form['status'] != 'pending') disabled @endif>
            @error('form.from_name')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>

    {{-- 제목 필드 --}}
    <div>
        <label for="subject" class="block text-xs font-medium text-gray-700 mb-1">
            제목 <span class="text-red-500">*</span>
        </label>
        <input type="text" 
               wire:model="form.subject" 
               id="subject" 
               name="subject"
               class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
               placeholder="이메일 제목을 입력하세요"
               @if(isset($form['status']) && $form['status'] != 'pending') disabled @endif
               required>
        @error('form.subject')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 내용 필드 --}}
    <div>
        <label for="body" class="block text-xs font-medium text-gray-700 mb-1">
            내용 <span class="text-red-500">*</span>
        </label>
        <textarea wire:model="form.body" 
                  id="body" 
                  name="body"
                  rows="10"
                  class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="이메일 내용을 작성하세요"
                  @if(isset($form['status']) && $form['status'] != 'pending') disabled @endif
                  required></textarea>
        @error('form.body')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- 에러 메시지 표시 --}}
    @if(isset($form['error_message']) && $form['error_message'])
    <div class="bg-red-50 border border-red-200 rounded-lg p-3">
        <div class="flex">
            <svg class="w-5 h-5 text-red-400 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <div>
                <h3 class="text-sm font-medium text-red-800">발송 오류</h3>
                <p class="mt-1 text-xs text-red-700">{{ $form['error_message'] }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- 메타 정보 표시 --}}
    <div class="pt-4 border-t border-gray-200">
        <dl class="space-y-2">
            @if(isset($form['sent_at']) && $form['sent_at'])
            <div class="flex justify-between text-xs">
                <dt class="text-gray-500">발송시간:</dt>
                <dd class="text-gray-900">{{ \Carbon\Carbon::parse($form['sent_at'])->format('Y-m-d H:i:s') }}</dd>
            </div>
            @endif
            @if(isset($form['opened_at']) && $form['opened_at'])
            <div class="flex justify-between text-xs">
                <dt class="text-gray-500">열람시간:</dt>
                <dd class="text-gray-900">{{ \Carbon\Carbon::parse($form['opened_at'])->format('Y-m-d H:i:s') }} ({{ $form['open_count'] ?? 0 }}회)</dd>
            </div>
            @endif
            @if(isset($form['clicked_at']) && $form['clicked_at'])
            <div class="flex justify-between text-xs">
                <dt class="text-gray-500">클릭시간:</dt>
                <dd class="text-gray-900">{{ \Carbon\Carbon::parse($form['clicked_at'])->format('Y-m-d H:i:s') }} ({{ $form['click_count'] ?? 0 }}회)</dd>
            </div>
            @endif
            @if(isset($form['created_at']))
            <div class="flex justify-between text-xs">
                <dt class="text-gray-500">생성일:</dt>
                <dd class="text-gray-900">{{ \Carbon\Carbon::parse($form['created_at'])->format('Y-m-d H:i:s') }}</dd>
            </div>
            @endif
            @if(isset($form['updated_at']))
            <div class="flex justify-between text-xs">
                <dt class="text-gray-500">수정일:</dt>
                <dd class="text-gray-900">{{ \Carbon\Carbon::parse($form['updated_at'])->format('Y-m-d H:i:s') }}</dd>
            </div>
            @endif
        </dl>
    </div>
</div>