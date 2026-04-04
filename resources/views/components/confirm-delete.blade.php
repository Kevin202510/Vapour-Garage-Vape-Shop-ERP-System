@props(['name', 'action', 'itemName' => 'this record'])

<x-modal :name="$name" title="Confirm Deletion" max-width="sm">
    <p class="text-[13px] text-ink3 leading-relaxed">
        Are you sure you want to delete <strong class="text-ink">{{ $itemName }}</strong>?
        This action cannot be undone.
    </p>

    <x-slot:footer>
        <button onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: '{{ $name }}' }))"
                class="inline-flex items-center px-4 py-2 text-[12px] font-medium text-ink3 border border-cream3 rounded-md hover:bg-cream transition-colors">
            Cancel
        </button>
        <form method="POST" action="{{ $action }}">
            @csrf
            @method('DELETE')
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 text-[12px] font-medium bg-danger/10 text-danger border border-danger/30 rounded-md hover:bg-danger/20 transition-colors">
                Delete
            </button>
        </form>
    </x-slot:footer>
</x-modal>
