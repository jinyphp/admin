<x-flex-between>
    <div class="page-title-box">
        <x-flex class="align-items-center gap-2">
            <h1 class="align-middle h3 d-inline">
                @if (isset($actions['title']))
                {{$actions['title']}}
                @endif
            </h1>
            {{-- <x-badge-info>Admin</x-badge-info> --}}
        </x-flex>
        <p>
            @if (isset($actions['subtitle']))
                {{$actions['subtitle']}}
            @endif
        </p>
    </div>

    <div class="page-title-box">
        <x-breadcrumb-item>
            {{$actions['route']['uri']}}
        </x-breadcrumb-item>

        <div class="mt-2 d-flex justify-content-end gap-2">
            <button class="btn btn-danger">
                Video
            </button>

            <button class="btn btn-secondary">
                Manual
            </button>
        </div>
    </div>

</x-flex-between>
