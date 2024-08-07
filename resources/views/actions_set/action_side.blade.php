@livewire('setActionRule')

<script>
    document.addEventListener('livewire:init', () => {
        Livewire.on('refeshTable', (event) => {
            console.log("refeshTable");
            window.location.reload();
        });
    });
</script>
