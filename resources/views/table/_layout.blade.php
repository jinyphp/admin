<x-theme theme="admin.sidebar">
    <x-theme-layout>

        {{-- Title --}}
        @if (isset($actions['view']['title']))
            @includeIf($actions['view']['title'])
        @else
            @includeIf('jiny-admin::table.title')
        @endif


        {{-- CRUD 테이블 --}}
        <section>
            <main>
                @livewire('WireTable-PopupForm', [
                    'actions' => $actions,
                ])
            </main>
        </section>


    </x-theme-layout>
</x-theme>
