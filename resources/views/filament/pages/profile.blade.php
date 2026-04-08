<x-filament-panels::page>
    <x-filament-panels::form wire:submit="save">
        {{ $this->form }}

        <div class="flex flex-wrap gap-4 mt-8">
            {{-- Form Submit (Save Changes) --}}
            <x-filament-panels::form.actions
                :actions="$this->getFormActions()"
            />

            {{-- Standalone Page Action (Delete Account) --}}
            {{ $this->deleteAccountAction }}
        </div>
    </x-filament-panels::form>
    
    <x-filament-actions::modals />
</x-filament-panels::page>
