@props(['name', 'action', 'itemName' => 'this record'])

<x-modal :name="$name" title="Confirm Deletion" max-width="sm">
    <p style="font-size:13px;color:#c8c4d4;line-height:1.6;margin-bottom:4px">
        Delete <strong style="color:#e8e4f0">{{ $itemName }}</strong>?<br>
        <span style="font-family:'Share Tech Mono',monospace;font-size:11px;color:#7a7490">This action cannot be undone.</span>
    </p>
    <x-slot:footer>
        <button onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: '{{ $name }}' }))"
                style="padding:6px 16px;background:transparent;color:#c8c4d4;border:1px solid rgba(124,34,212,0.25);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.1em;cursor:pointer;transition:all 0.2s;text-transform:uppercase">
            Cancel
        </button>
        <form method="POST" action="{{ $action }}">
            @csrf @method('DELETE')
            <button type="submit"
                    style="padding:6px 16px;background:rgba(184,50,50,0.15);color:#e05050;border:1px solid rgba(184,50,50,0.35);border-radius:3px;font-family:'Share Tech Mono',monospace;font-size:11px;letter-spacing:0.1em;cursor:pointer;transition:all 0.2s;text-transform:uppercase">
                Delete
            </button>
        </form>
    </x-slot:footer>
</x-modal>
