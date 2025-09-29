{{--
    JSON Tree Display Partial
    Recursively displays JSON data in a collapsible tree format
--}}
<ul class="{{ $level === 0 ? '' : 'json-collapsible' }}">
    @foreach($data as $key => $value)
        <li>
            @if(is_array($value) || is_object($value))
                {{-- Object/Array with children --}}
                <span class="json-toggle expanded" onclick="toggleJsonNode(this)">▼</span>
                <span class="json-key text-xs">"{{ $key }}"</span>: 
                @if(is_array($value))
                    <span class="text-xs text-gray-600 dark:text-gray-400">[{{ count($value) }} items]</span>
                @else
                    <span class="text-xs text-gray-600 dark:text-gray-400">{object}</span>
                @endif
                
                <div class="json-collapsible">
                    @include('jiny-admin::admin.partials.json-tree', ['data' => $value, 'level' => $level + 1])
                </div>
            @else
                {{-- Leaf value --}}
                <span class="json-key text-xs">"{{ $key }}"</span>: 
                @if(is_string($value))
                    <span class="json-string text-xs">"{{ $value }}"</span>
                @elseif(is_numeric($value))
                    <span class="json-number text-xs">{{ $value }}</span>
                @elseif(is_bool($value))
                    <span class="json-boolean text-xs">{{ $value ? 'true' : 'false' }}</span>
                @elseif(is_null($value))
                    <span class="json-null text-xs">null</span>
                @else
                    <span class="text-xs text-gray-700 dark:text-gray-300">{{ $value }}</span>
                @endif
            @endif
        </li>
    @endforeach
</ul>

<script>
function toggleJsonNode(toggle) {
    const expanded = toggle.classList.contains('expanded');
    const target = toggle.parentElement.querySelector('.json-collapsible');
    
    if (expanded) {
        toggle.classList.remove('expanded');
        toggle.textContent = '▶';
        if (target) target.classList.add('json-collapsed');
    } else {
        toggle.classList.add('expanded');
        toggle.textContent = '▼';
        if (target) target.classList.remove('json-collapsed');
    }
}
</script>