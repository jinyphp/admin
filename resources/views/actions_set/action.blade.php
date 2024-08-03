<div class="bg-gray-300 p-2">
    <div class="container">
        <div>{{$slot}}</div>
        @livewire('setActionRule')
    </div>
</div>

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('refeshTable', (event) => {
            console.log("refeshTable");
            window.location.reload();
        });
    });
</script>
