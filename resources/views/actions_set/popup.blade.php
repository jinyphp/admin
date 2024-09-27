<div>
    {{-- loading 화면 처리 --}}
    <x-loading-indicator />

    <style>
        .floating-buttons {
            transform: translateX(50%) rotate(-90deg);
        }

        .animate-rotate:hover .animate-target,
        .animate-rotate:focus-visible .animate-target {
            animation: rotate 0.45s ease-in-out;
        }

        .top-30 {
            top: 350px;
            !important;
        }
    </style>

    <!-- Customizer toggle -->
    <div class="floating-buttons position-fixed top-30 end-0 z-sticky me-3 me-xl-4 pb-4">
        <a class="btn btn-sm
            btn-outline-primary
            text-uppercase bg-body
            rounded-pill shadow
            animate-rotate ms-2 me-n5"
            href="javascript:void(0)" wire:click="popupRuleOpen()" style="font-size: .625rem; letter-spacing: .05rem;"
            role="button" aria-controls="customizer">
            Actions <i class="ci-settings fs-base ms-1 me-n2 animate-target"></i>
        </a>
    </div>

    {{-- <div class="d-flex">
        <button class="btn btn-primary" wire:click="popupRuleOpen()">
            <div class="d-flex gap-2">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-gear" viewBox="0 0 16 16">
                    <path d="M8 4.754a3.246 3.246 0 1 0 0 6.492 3.246 3.246 0 0 0 0-6.492M5.754 8a2.246 2.246 0 1 1 4.492 0 2.246 2.246 0 0 1-4.492 0"/>
                    <path d="M9.796 1.343c-.527-1.79-3.065-1.79-3.592 0l-.094.319a.873.873 0 0 1-1.255.52l-.292-.16c-1.64-.892-3.433.902-2.54 2.541l.159.292a.873.873 0 0 1-.52 1.255l-.319.094c-1.79.527-1.79 3.065 0 3.592l.319.094a.873.873 0 0 1 .52 1.255l-.16.292c-.892 1.64.901 3.434 2.541 2.54l.292-.159a.873.873 0 0 1 1.255.52l.094.319c.527 1.79 3.065 1.79 3.592 0l.094-.319a.873.873 0 0 1 1.255-.52l.292.16c1.64.893 3.434-.902 2.54-2.541l-.159-.292a.873.873 0 0 1 .52-1.255l.319-.094c1.79-.527 1.79-3.065 0-3.592l-.319-.094a.873.873 0 0 1-.52-1.255l.16-.292c.893-1.64-.902-3.433-2.541-2.54l-.292.159a.873.873 0 0 1-1.255-.52zm-2.633.283c.246-.835 1.428-.835 1.674 0l.094.319a1.873 1.873 0 0 0 2.693 1.115l.291-.16c.764-.415 1.6.42 1.184 1.185l-.159.292a1.873 1.873 0 0 0 1.116 2.692l.318.094c.835.246.835 1.428 0 1.674l-.319.094a1.873 1.873 0 0 0-1.115 2.693l.16.291c.415.764-.42 1.6-1.185 1.184l-.291-.159a1.873 1.873 0 0 0-2.693 1.116l-.094.318c-.246.835-1.428.835-1.674 0l-.094-.319a1.873 1.873 0 0 0-2.692-1.115l-.292.16c-.764.415-1.6-.42-1.184-1.185l.159-.291A1.873 1.873 0 0 0 1.945 8.93l-.319-.094c-.835-.246-.835-1.428 0-1.674l.319-.094A1.873 1.873 0 0 0 3.06 4.377l-.16-.292c-.415-.764.42-1.6 1.185-1.184l.292.159a1.873 1.873 0 0 0 2.692-1.115z"/>
                </svg>
                <div>
                    SetActions
                </div>
            </div>
        </button>
        <div>

        </div>
    </div> --}}




    <!-- 팝업 Rule 수정창 -->
    @if ($popupRule)
        <x-dialog-modal wire:model="popupRule" maxWidth="2xl">
            <x-slot name="title">
                {{ $actionPath }}
            </x-slot>
            <x-slot name="content">
                @includeIf($viewForms)
            </x-slot>

            <x-slot name="footer">
                <div class="flex justify-between">
                    @if (isset($actions['id']))
                        <div>
                        </div>
                        <div>
                            <x-button secondary wire:click="popupRuleClose">취소</x-button>
                            <x-button primary wire:click="update">수정</x-button>
                        </div>
                    @else
                        <div></div>
                        <div class="text-right">
                            <x-button secondary wire:click="popupRuleClose">취소</x-button>
                            <x-button primary wire:click="save">저장</x-button>
                        </div>
                    @endif
                </div>
            </x-slot>
        </x-dialog-modal>
    @endif


    @if ($popupResourceEdit)
        <x-dialog-modal wire:model="popupResourceEdit" maxWidth="2xl">
            <x-slot name="content">
                {!! xTextarea()->setWire('model.defer', 'content') !!}
            </x-slot>

            <x-slot name="footer">
                <div class="flex justify-between">
                    <div></div>
                    <div class="text-right">
                        <x-button secondary wire:click="returnRule">취소</x-button>
                        <x-button primary wire:click="update">수정</x-button>
                    </div>
                </div>
            </x-slot>
        </x-dialog-modal>
    @endif


    {{-- 레이아웃 --}}
    @if ($design == 'layout')
        <x-dialog-modal wire:model="popupForms" :maxWidth="$popupWindowWidth">
            <x-slot name="title">
                {{ __('레이아웃 변경') }}
            </x-slot>

            <x-slot name="content">

                @includeIf('jiny-admin::actions_set.form_dynamic_layout')

            </x-slot>

            <x-slot name="footer">
                <div class="flex justify-between">
                    <div></div>
                    <div class="text-right">
                        <x-button secondary wire:click="close">취소</x-button>
                        <x-button primary wire:click="save">저장</x-button>
                    </div>
                </div>
            </x-slot>
        </x-dialog-modal>
    @endif

    {{-- 레이아웃 --}}
    @if ($design == 'action')
        <x-dialog-modal wire:model="popupForms" :maxWidth="$popupWindowWidth">
            <x-slot name="title">
                {{ __('Action Rules') }} : {{ $actionPath }}
            </x-slot>

            <x-slot name="content">

                {{-- @includeIf('jiny-admin::actions_set.form_dynamic_layout') --}}
                @includeIf($viewForms)
            </x-slot>

            <x-slot name="footer">
                <div class="flex justify-between">
                    <div></div>
                    <div class="text-right">
                        <x-button secondary wire:click="close">취소</x-button>
                        <x-button primary wire:click="save">저장</x-button>
                    </div>
                </div>
            </x-slot>
        </x-dialog-modal>
    @endif

</div>
