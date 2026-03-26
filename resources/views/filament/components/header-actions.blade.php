<div class="flex items-center gap-x-4">
    <!-- Scanner Button -->
    <a href="{{ \App\Filament\Pages\ScanAsset::getUrl() }}" 
       class="flex items-center justify-center p-2 text-gray-500 hover:text-primary-600 transition-colors bg-gray-100 dark:bg-gray-800 rounded-xl group hover:shadow-sm"
       title="Scan Asset">
        <x-heroicon-o-qr-code class="w-6 h-6 group-hover:scale-110 transition-transform" />
    </a>

    @php
        $targetLocale = app()->getLocale() === 'en' ? 'ar' : 'en';
    @endphp
    <!-- Language Switcher Toggle -->
    <a href="{{ route('locale.switch', $targetLocale) }}" 
       class="flex items-center justify-center p-2 text-gray-500 hover:text-primary-600 transition-colors bg-gray-100 dark:bg-gray-800 rounded-xl group hover:shadow-sm"
       title="Switch to {{ strtoupper($targetLocale) }}">
        <x-heroicon-o-globe-alt class="w-6 h-6 group-hover:rotate-12 transition-transform" />
    </a>
</div>
