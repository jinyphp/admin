{{--
    AdminUsers 테이블 뷰
    Tailwind CSS 스타일 적용 및 Livewire 기능 통합
--}}
<div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
            <tr>
                <th scope="col" class="px-3 py-2 text-left">
                    <input type="checkbox"
                           wire:model.live="selectedAll"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('id')" class="flex items-center">
                        ID
                        @if($sortField === 'id')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('name')" class="flex items-center">
                        이름
                        @if($sortField === 'name')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('email')" class="flex items-center">
                        이메일
                        @if($sortField === 'email')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('utype')" class="flex items-center">
                        사용자 유형
                        @if($sortField === 'utype')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    인증
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('two_factor_enabled')" class="flex items-center">
                        2FA
                        @if($sortField === 'two_factor_enabled')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('login_count')" class="flex items-center">
                        접속 횟수
                        @if($sortField === 'login_count')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('password_expires_at')" class="flex items-center">
                        비밀번호 만료
                        @if($sortField === 'password_expires_at')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="px-3 py-2 text-left text-xs font-medium text-gray-600 uppercase">
                    <button wire:click="sortBy('created_at')" class="flex items-center">
                        가입일
                        @if($sortField === 'created_at')
                            @if($sortDirection === 'asc')
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M14.707 10.293a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 111.414-1.414L10 13.586l3.293-3.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-3 h-3 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 9.707a1 1 0 010-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 01-1.414 1.414L10 6.414l-3.293 3.293a1 1 0 01-1.414 0z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        @endif
                    </button>
                </th>
                <th scope="col" class="relative px-3 py-2">
                    <span class="sr-only">Actions</span>
                </th>
            </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
            @forelse($rows as $item)
            <tr class="hover:bg-gray-50">
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <input type="checkbox"
                           wire:model.live="selected"
                           value="{{ $item->id }}"
                           class="h-3.5 w-3.5 text-blue-600 focus:ring-1 focus:ring-blue-500 border-gray-200 rounded">
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                    {{ $item->id }}
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 h-8 w-8 mr-2">
                            @if(!empty($item->avatar))
                                <img class="h-8 w-8 rounded-full object-cover"
                                     src="{{ $item->avatar }}"
                                     alt="{{ $item->name }}"
                                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%2232%22 height=%2232%22%3E%3Ccircle cx=%2216%22 cy=%2216%22 r=%2216%22 fill=%22%236366f1%22/%3E%3Ctext x=%2216%22 y=%2222%22 text-anchor=%22middle%22 fill=%22white%22 font-size=%2216%22 font-weight=%22bold%22%3E{{ mb_substr($item->name ?? 'U', 0, 1) }}%3C/text%3E%3C/svg%3E';">
                            @else
                                <div class="h-8 w-8 rounded-full flex items-center justify-center text-white font-semibold text-xs"
                                     style="background-color: #6366f1;">
                                    {{ mb_substr($item->name ?? 'U', 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <div class="min-w-0">
                            <a href="{{ route('admin.system.users.show', $item->id) }}"
                               class="text-xs text-blue-600 hover:text-blue-900 font-medium block truncate">
                                {{ $item->name ?? '' }}
                            </a>
                            @if(!empty($item->username))
                                <div class="text-xs text-gray-400">@{{ $item->username }}</div>
                            @endif
                        </div>
                    </div>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    <span class="text-xs text-gray-900">{{ $item->email ?? '' }}</span>
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @php
                        $utypeRoute = $jsonData['index']['table']['columns']['utype']['linkRoute'] ?? null;
                    @endphp
                    @if(isset($item->utype_name) && $item->utype_name)
                        @if($utypeRoute)
                            <a href="{{ route($utypeRoute) }}"
                               class="text-xs text-blue-600 hover:text-blue-900 font-medium">
                                {{ $item->utype_name }}
                            </a>
                        @else
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-purple-100 text-purple-800">
                                {{ $item->utype_name }}
                            </span>
                        @endif
                    @elseif($item->utype)
                        @if($utypeRoute)
                            <a href="{{ route($utypeRoute) }}"
                               class="text-xs text-blue-500 hover:text-blue-700">
                                {{ $item->utype }}
                            </a>
                        @else
                            <span class="text-xs text-gray-500">{{ $item->utype }}</span>
                        @endif
                    @else
                        @if(isset($item->isAdmin) && $item->isAdmin)
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-600">
                                관리자
                            </span>
                        @else
                            <span class="text-xs text-gray-400">-</span>
                        @endif
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->email_verified_at)
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-green-100 text-green-800">
                            인증됨
                        </span>
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-yellow-100 text-yellow-800">
                            미인증
                        </span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @if($item->two_factor_enabled)
                        <div class="flex items-center space-x-1">
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-blue-100 text-blue-800">
                                활성화
                            </span>
                            <a href="{{ route('admin.system.user.2fa.edit', $item->id) }}"
                               class="text-blue-600 hover:text-blue-900"
                               title="2FA 관리">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </a>
                        </div>
                    @else
                        <div class="flex items-center space-x-1">
                            <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-600">
                                비활성
                            </span>
                            <a href="{{ route('admin.system.user.2fa.edit', $item->id) }}"
                               class="text-gray-400 hover:text-gray-600"
                               title="2FA 설정">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-900">
                    @if(isset($item->login_count))
                        <span class="font-medium">{{ $item->login_count }}</span>회
                    @else
                        <span class="text-gray-400">0회</span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap">
                    @php
                        $user = \Jiny\Admin\Models\User::find($item->id);
                        $passwordStatus = $user ? $user->password_expiry_status : 'active';
                        $daysUntilExpiry = $user ? $user->days_until_password_expiry : null;
                    @endphp

                    @if($passwordStatus === 'expired')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-red-100 text-red-800">
                            만료됨
                        </span>
                    @elseif($passwordStatus === 'expiring_soon')
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-yellow-100 text-yellow-800"
                              title="{{ $daysUntilExpiry }}일 후 만료">
                            {{ $daysUntilExpiry }}일 남음
                        </span>
                    @elseif($item->password_expires_at)
                        <span class="text-xs text-gray-500" title="만료일: {{ \Carbon\Carbon::parse($item->password_expires_at)->format('Y-m-d') }}">
                            {{ \Carbon\Carbon::parse($item->password_expires_at)->format('m/d') }}
                        </span>
                    @else
                        <span class="px-1.5 inline-flex text-xs leading-4 font-medium rounded-full bg-gray-100 text-gray-600">
                            무제한
                        </span>
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-xs text-gray-500">
                    @if(isset($item->created_at) && $item->created_at)
                        {{ \Carbon\Carbon::parse($item->created_at)->format('Y-m-d') }}
                    @else
                        -
                    @endif
                </td>
                <td class="px-3 py-2.5 whitespace-nowrap text-right text-xs font-medium">
                    <div class="flex items-center space-x-1">
                        <a href="{{ route('admin.system.users.show', $item->id) }}"
                           class="text-gray-600 hover:text-gray-900">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                            </svg>
                        </a>
                        <a href="{{ route('admin.system.users.edit', $item->id) }}"
                           class="text-blue-600 hover:text-blue-900">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path>
                            </svg>
                        </a>
                        <button wire:click="requestDeleteSingle({{ $item->id }})"
                                class="text-red-600 hover:text-red-900">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                        </button>
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="11" class="px-3 py-4 text-center text-xs text-gray-500">
                    사용자가 없습니다.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
