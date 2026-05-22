@php
    $tabs = $this->getTabs();
@endphp

<x-filament-widgets::widget>
    <x-filament::section :compact="false">
        <x-slot name="heading">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-rectangle-stack" class="w-5 h-5 text-indigo-500" />
                    <span>{{ __('Activity') }}</span>
                </div>

                <div class="inline-flex p-1 rounded-lg bg-slate-100 dark:bg-slate-800/60 ring-1 ring-slate-200 dark:ring-white/10">
                    @foreach ($tabs as $key => $tab)
                        @php $active = $activeTab === $key; @endphp
                        <button
                            type="button"
                            wire:click="setActiveTab('{{ $key }}')"
                            class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs sm:text-sm font-medium rounded-md transition-all duration-150 {{ $active
                                ? 'bg-white dark:bg-slate-700 text-indigo-600 dark:text-indigo-300 shadow-sm ring-1 ring-slate-200 dark:ring-white/10'
                                : 'text-slate-500 dark:text-slate-400 hover:text-slate-700 dark:hover:text-slate-200' }}"
                        >
                            <x-filament::icon :icon="$tab['icon']" class="w-4 h-4" />
                            <span>{{ $tab['label'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </x-slot>

        <div wire:key="activity-tab-{{ $activeTab }}">
            {{ $this->table }}
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
