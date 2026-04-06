<div class="flex items-center gap-x-4">
    <!-- Scanner Modal Toggle -->
    <x-filament::modal id="scanner-modal" width="lg" alignment="center">
        <x-slot name="trigger">
            <button class="flex items-center justify-center p-2 text-gray-500 hover:text-primary-600 transition-colors bg-gray-100 dark:bg-gray-800 rounded-xl group hover:shadow-sm"
                    title="{{ __('Scan Asset') }}">
                <x-heroicon-o-qr-code class="w-6 h-6 group-hover:scale-110 transition-transform" />
            </button>
        </x-slot>

        <x-slot name="heading">
            {{ __('Scan or Search Asset') }}
        </x-slot>

        <livewire:asset-scanner />
    </x-filament::modal>

    @php
        $targetLocale = app()->getLocale() === 'en' ? 'ar' : 'en';
    @endphp
    <!-- Language Switcher Toggle -->
    <a href="{{ route('locale.switch', $targetLocale) }}" 
       class="flex items-center justify-center p-2 text-gray-500 hover:text-primary-600 transition-colors bg-gray-100 dark:bg-gray-800 rounded-xl group hover:shadow-sm"
       title="{{ __('Switch to') }} {{ strtoupper($targetLocale) }}">
        <x-heroicon-o-globe-alt class="w-6 h-6 group-hover:rotate-12 transition-transform" />
    </a>
</div>
