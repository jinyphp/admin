<a href="{{ route('admin.users.show', Auth::id()) }}" class="flex items-center gap-x-4 px-6 py-3 text-sm/6 font-semibold text-white hover:bg-white/5">
    @if(auth()->user() && auth()->user()->avatar && auth()->user()->avatar !== '/images/default-avatar.png')
        <img src="{{ auth()->user()->avatar }}" 
             alt="{{ auth()->user()->name }}" 
             class="size-8 rounded-full object-cover outline -outline-offset-1 outline-white/10"
             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'size-8 rounded-full bg-gray-800 flex items-center justify-center text-white text-sm font-medium outline -outline-offset-1 outline-white/10\'>{{ mb_strtoupper(mb_substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}</div><span aria-hidden=\'true\'>{{ auth()->user()->name ?? \'Admin User\' }}</span>';">
    @else
        @php
            $userName = auth()->user() ? (auth()->user()->name ?? auth()->user()->email ?? 'Admin') : 'Admin';
            $initial = mb_strtoupper(mb_substr($userName, 0, 1));
            $colors = ['bg-red-600', 'bg-yellow-600', 'bg-green-600', 'bg-blue-600', 'bg-indigo-600', 'bg-purple-600', 'bg-pink-600'];
            $colorIndex = crc32($userName) % count($colors);
            $bgColor = $colors[$colorIndex];
        @endphp
        <div class="size-8 rounded-full {{ $bgColor }} flex items-center justify-center text-white text-sm font-medium outline -outline-offset-1 outline-white/10">
            {{ $initial }}
        </div>
    @endif
    <span class="sr-only">Your profile</span>
    <span aria-hidden="true">{{ auth()->user() ? (auth()->user()->name ?? 'Admin User') : 'Admin User' }}</span>
</a>