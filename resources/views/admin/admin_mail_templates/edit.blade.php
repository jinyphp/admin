{{-- Email Template 수정 폼 --}}
<div class="space-y-6">
    {{-- Instructions --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-800 mb-2">이메일 템플릿 수정 가이드</h3>
        <ul class="text-xs text-blue-700 space-y-1">
            <li>• <strong>변수 사용:</strong> 템플릿에서 [variable_name] 형식으로 변수를 사용할 수 있습니다</li>
            <li>• <strong>자주 사용되는 변수:</strong> [user_name], [email], [date], [company_name], [url]</li>
            <li>• <strong>HTML 지원:</strong> HTML 태그를 사용하여 서식을 지정할 수 있습니다</li>
            <li>• <strong>Slug 변경:</strong> Slug를 변경할 때는 기존 연동된 시스템에 영향이 없는지 확인해주세요</li>
        </ul>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Left Column --}}
        <div class="space-y-4">
            {{-- Name 필드 --}}
            <div>
                <label for="name" class="block text-xs font-medium text-gray-700 mb-1">
                    템플릿 이름 <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       wire:model="form.name" 
                       id="name" 
                       name="name"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="예: 회원가입 환영 메일"
                       required>
                @error('form.name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Slug 필드 --}}
            <div>
                <label for="slug" class="block text-xs font-medium text-gray-700 mb-1">
                    Slug <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       wire:model="form.slug" 
                       id="slug" 
                       name="slug"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="예: welcome-email"
                       required>
                <p class="mt-1 text-xs text-gray-500">URL에 사용되는 고유 식별자 (영문, 숫자, 하이픈만 사용)</p>
                @error('form.slug')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Subject 필드 --}}
            <div>
                <label for="subject" class="block text-xs font-medium text-gray-700 mb-1">
                    제목 <span class="text-red-500">*</span>
                </label>
                <input type="text" 
                       wire:model="form.subject" 
                       id="subject" 
                       name="subject"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="예: [user_name]님, 환영합니다!"
                       required>
                @error('form.subject')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Type 필드 --}}
            <div>
                <label for="type" class="block text-xs font-medium text-gray-700 mb-1">
                    타입
                </label>
                <select wire:model="form.type" 
                        id="type" 
                        name="type"
                        class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="html">HTML</option>
                    <option value="text">Text</option>
                    <option value="markdown">Markdown</option>
                </select>
                @error('form.type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Category 필드 --}}
            <div>
                <label for="category" class="block text-xs font-medium text-gray-700 mb-1">
                    카테고리
                </label>
                <input type="text" 
                       wire:model="form.category" 
                       id="category" 
                       name="category"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="예: 회원가입, 비밀번호 재설정, 공지사항">
                @error('form.category')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Status 필드 --}}
            <div>
                <label for="status" class="block text-xs font-medium text-gray-700 mb-1">
                    상태
                </label>
                <select wire:model="form.status" 
                        id="status" 
                        name="status"
                        class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="1">활성</option>
                    <option value="0">비활성</option>
                </select>
                @error('form.status')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-4">
            {{-- From Name 필드 --}}
            <div>
                <label for="from_name" class="block text-xs font-medium text-gray-700 mb-1">
                    발신자 이름
                </label>
                <input type="text" 
                       wire:model="form.from_name" 
                       id="from_name" 
                       name="from_name"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="예: 고객서비스팀">
                @error('form.from_name')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- From Email 필드 --}}
            <div>
                <label for="from_email" class="block text-xs font-medium text-gray-700 mb-1">
                    발신자 이메일
                </label>
                <input type="email" 
                       wire:model="form.from_email" 
                       id="from_email" 
                       name="from_email"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="예: noreply@company.com">
                @error('form.from_email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reply To 필드 --}}
            <div>
                <label for="reply_to" class="block text-xs font-medium text-gray-700 mb-1">
                    회신 주소
                </label>
                <input type="email" 
                       wire:model="form.reply_to" 
                       id="reply_to" 
                       name="reply_to"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="예: support@company.com">
                @error('form.reply_to')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Priority 필드 --}}
            <div>
                <label for="priority" class="block text-xs font-medium text-gray-700 mb-1">
                    우선순위
                </label>
                <select wire:model="form.priority" 
                        id="priority" 
                        name="priority"
                        class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="0">보통</option>
                    <option value="1">높음</option>
                    <option value="-1">낮음</option>
                </select>
                @error('form.priority')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Variables 필드 --}}
            <div>
                <label for="variables" class="block text-xs font-medium text-gray-700 mb-1">
                    사용 가능한 변수 (JSON)
                </label>
                <textarea wire:model="form.variables" 
                          id="variables" 
                          name="variables"
                          rows="3"
                          class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                          placeholder='["user_name", "email", "date", "company_name"]'></textarea>
                <p class="mt-1 text-xs text-gray-500">JSON 배열 형식으로 입력</p>
                @error('form.variables')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- 메타 정보 표시 --}}
            @if(isset($form['created_at']) || isset($form['updated_at']))
            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3">
                <h4 class="text-xs font-medium text-gray-700 mb-2">메타 정보</h4>
                <dl class="space-y-1">
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
            @endif
        </div>
    </div>

    {{-- Body 필드 (Full Width) --}}
    <div>
        <label for="body" class="block text-xs font-medium text-gray-700 mb-1">
            메일 내용 <span class="text-red-500">*</span>
        </label>
        <textarea wire:model="form.body" 
                  id="body" 
                  name="body"
                  rows="12"
                  class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="안녕하세요 [user_name]님,

저희 서비스에 가입해 주셔서 감사합니다.
아래 링크를 클릭하여 회원가입을 완료해 주세요.

[activation_url]

감사합니다.
[company_name] 팀"
                  required></textarea>
        @error('form.body')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>

    {{-- Description 필드 --}}
    <div>
        <label for="description" class="block text-xs font-medium text-gray-700 mb-1">
            설명
        </label>
        <textarea wire:model="form.description" 
                  id="description" 
                  name="description"
                  rows="2"
                  class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                  placeholder="이 템플릿의 용도와 사용 시기를 설명해 주세요"></textarea>
        @error('form.description')
            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>