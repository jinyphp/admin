{{-- Email Template 생성 폼 --}}
<div class="space-y-6">
    {{-- Instructions --}}
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="text-sm font-medium text-blue-800 mb-2">이메일 템플릿 생성 가이드</h3>
        <ul class="text-xs text-blue-700 space-y-1">
            <li>• <strong>변수 사용:</strong> 템플릿에서 [variable_name] 형식으로 변수를 사용할 수 있습니다</li>
            <li>• <strong>자주 사용되는 변수:</strong> [user_name], [email], [date], [company_name], [url]</li>
            <li>• <strong>HTML 지원:</strong> HTML 태그를 사용하여 서식을 지정할 수 있습니다</li>
            <li>• <strong>Slug:</strong> URL에 사용되는 고유 식별자로 자동 생성됩니다</li>
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

            {{-- Category 필드 --}}
            <div>
                <label for="category" class="block text-xs font-medium text-gray-700 mb-1">
                    카테고리
                </label>
                <select wire:model="form.category" 
                        id="category" 
                        name="category"
                        class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="">선택하세요</option>
                    <option value="system">시스템</option>
                    <option value="marketing">마케팅</option>
                    <option value="transactional">거래</option>
                    <option value="notification">알림</option>
                    <option value="welcome">환영</option>
                    <option value="password">비밀번호</option>
                    <option value="verification">인증</option>
                </select>
                @error('form.category')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Type 필드 --}}
            <div>
                <label for="type" class="block text-xs font-medium text-gray-700 mb-1">
                    템플릿 타입
                </label>
                <select wire:model="form.type" 
                        id="type" 
                        name="type"
                        class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">
                    <option value="html">HTML</option>
                    <option value="text">텍스트</option>
                    <option value="markdown">마크다운</option>
                </select>
                @error('form.type')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Priority 필드 --}}
            <div>
                <label for="priority" class="block text-xs font-medium text-gray-700 mb-1">
                    우선순위
                </label>
                <input type="number" 
                       wire:model="form.priority" 
                       id="priority" 
                       name="priority"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="0"
                       value="0">
                @error('form.priority')
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
                       placeholder="예: support@company.com">
                @error('form.from_email')
                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                @enderror
            </div>

            {{-- Reply To 필드 --}}
            <div>
                <label for="reply_to" class="block text-xs font-medium text-gray-700 mb-1">
                    회신 이메일
                </label>
                <input type="email" 
                       wire:model="form.reply_to" 
                       id="reply_to" 
                       name="reply_to"
                       class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                       placeholder="예: reply@company.com">
                @error('form.reply_to')
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
    </div>

    {{-- Full Width Fields --}}
    <div class="space-y-4 mt-6">
        {{-- Subject 필드 --}}
        <div>
            <label for="subject" class="block text-xs font-medium text-gray-700 mb-1">
                이메일 제목 <span class="text-red-500">*</span>
            </label>
            <input type="text" 
                   wire:model="form.subject" 
                   id="subject" 
                   name="subject"
                   class="block w-full h-8 px-2.5 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                   placeholder="예: [company_name]에 오신 것을 환영합니다!"
                   required>
            @error('form.subject')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Variables 필드 --}}
        <div>
            <label for="variables" class="block text-xs font-medium text-gray-700 mb-1">
                사용 가능한 변수 (JSON 형식)
            </label>
            <textarea wire:model="form.variables" 
                      id="variables" 
                      name="variables"
                      rows="3"
                      class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder='예: {"user_name": "사용자명", "email": "이메일", "date": "날짜", "company_name": "회사명"}'></textarea>
            <p class="mt-1 text-xs text-gray-500">템플릿에서 사용할 수 있는 변수를 JSON 형식으로 정의합니다</p>
            @error('form.variables')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>

        {{-- Body 필드 --}}
        <div>
            <label for="body" class="block text-xs font-medium text-gray-700 mb-1">
                이메일 본문 <span class="text-red-500">*</span>
            </label>
            <textarea wire:model="form.body" 
                      id="body" 
                      name="body"
                      rows="10"
                      class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="이메일 내용을 입력하세요. HTML 태그와 변수를 사용할 수 있습니다."
                      required></textarea>
            <p class="mt-1 text-xs text-gray-500">HTML 태그와 [variable_name] 형식의 변수를 사용할 수 있습니다</p>
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
                      rows="3"
                      class="block w-full px-2.5 py-2 text-xs border border-gray-200 rounded focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500"
                      placeholder="템플릿에 대한 설명을 입력하세요"></textarea>
            @error('form.description')
                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
            @enderror
        </div>
    </div>
</div>