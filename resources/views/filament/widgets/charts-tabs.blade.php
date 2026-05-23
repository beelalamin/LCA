@php
    $tabs = $this->getTabs();
    $current = $tabs[$activeTab] ?? reset($tabs);
    $chartCount = count($current['charts']);
    $isRtl = app()->getLocale() === 'ar';
@endphp

<x-filament-widgets::widget>
    <div class="space-y-4" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
        <div class="flex flex-wrap items-center justify-between gap-3 px-1">
            <div class="flex items-center gap-2">
                <x-filament::icon :icon="$current['icon']" class="w-5 h-5 text-primary-600 dark:text-primary-400" />
                <span class="text-base font-semibold text-gray-800 dark:text-gray-100">{{ $current['label'] }}</span>
            </div>

            <div class="inline-flex p-1 rounded-lg bg-gray-100 dark:bg-gray-950 ring-1 ring-gray-200 dark:ring-white/10">
                @foreach ($tabs as $key => $tab)
                    @php $active = $activeTab === $key; @endphp
                    <button
                        type="button"
                        wire:click="setActiveTab('{{ $key }}')"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-medium rounded-md transition-all duration-150 {{ $active
                            ? 'bg-white dark:bg-gray-900 text-primary-600 dark:text-primary-400 shadow-sm ring-1 ring-gray-200 dark:ring-white/10'
                            : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200' }}"
                    >
                        <x-filament::icon :icon="$tab['icon']" class="w-4 h-4" />
                        <span>{{ $tab['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <div
            wire:key="charts-tab-{{ $activeTab }}"
            class="grid grid-cols-1 @if($chartCount > 1) lg:grid-cols-2 @endif gap-4"
        >
            @foreach ($current['charts'] as $chartClass)
                @livewire($chartClass, [], key('chart-' . $activeTab . '-' . \Illuminate\Support\Str::slug($chartClass)))
            @endforeach
        </div>
    </div>
</x-filament-widgets::widget>
