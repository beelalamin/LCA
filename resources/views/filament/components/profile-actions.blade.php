<div class="space-y-6">
    <div class="flex flex-wrap gap-4">
        {{-- Delete Account Button --}}
        <x-filament::button
            wire:click="deleteAccount"
            wire:confirm="{{ __('Are you sure you want to delete your account? This action cannot be undone.') }}"
            color="danger"
            icon="heroicon-o-trash"
        >
            {{ __('Delete Account') }}
        </x-filament::button>
    </div>
</div>
