@php
    /**
     * @var array $alerts
     * @var int $totalAlerts
     * @var int $urgentAlerts
     */
    $severityStyles = [
        'danger' => [
            'card' => 'bg-gradient-to-br from-rose-50 to-red-50 dark:from-rose-950/40 dark:to-red-950/30 ring-rose-200/70 dark:ring-rose-500/20 hover:ring-rose-300 dark:hover:ring-rose-400/40',
            'iconWrap' => 'bg-rose-100 text-rose-600 dark:bg-rose-500/20 dark:text-rose-300',
            'count' => 'text-rose-600 dark:text-rose-300',
            'pill' => 'bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200',
            'pillLabel' => __('Action required'),
            'bar' => 'bg-rose-500',
        ],
        'warning' => [
            'card' => 'bg-gradient-to-br from-amber-50 to-orange-50 dark:from-amber-950/30 dark:to-orange-950/20 ring-amber-200/70 dark:ring-amber-500/20 hover:ring-amber-300 dark:hover:ring-amber-400/40',
            'iconWrap' => 'bg-amber-100 text-amber-600 dark:bg-amber-500/20 dark:text-amber-300',
            'count' => 'text-amber-600 dark:text-amber-300',
            'pill' => 'bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-200',
            'pillLabel' => __('Watch'),
            'bar' => 'bg-amber-500',
        ],
        'neutral' => [
            'card' => 'bg-gradient-to-br from-slate-50 to-gray-50 dark:from-slate-900/50 dark:to-gray-900/40 ring-slate-200/70 dark:ring-slate-500/20 hover:ring-slate-300 dark:hover:ring-slate-400/40',
            'iconWrap' => 'bg-slate-100 text-slate-600 dark:bg-slate-500/20 dark:text-slate-300',
            'count' => 'text-slate-700 dark:text-slate-200',
            'pill' => 'bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-200',
            'pillLabel' => __('Review'),
            'bar' => 'bg-slate-400',
        ],
    ];
@endphp

<x-filament-widgets::widget>
    <x-filament::section
        class="ring-1 ring-amber-300/70 dark:ring-amber-500/30 hover:ring-amber-400/80 dark:hover:ring-amber-400/50 transition-shadow"
    >
        <x-slot name="heading">
            <div class="flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-bell-alert" class="w-5 h-5 text-amber-500" />
                <span>{{ __('Alerts & Actions') }}</span>
            </div>
        </x-slot>

        <x-slot name="description">
            <div class="flex flex-wrap items-center gap-3 text-xs">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-rose-100 text-rose-700 dark:bg-rose-500/20 dark:text-rose-200 font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                    {{ __('Urgent') }}: {{ number_format($urgentAlerts) }}
                </span>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-500/20 dark:text-slate-200 font-medium">
                    {{ __('Total flagged') }}: {{ number_format($totalAlerts) }}
                </span>
            </div>
        </x-slot>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            @foreach ($alerts as $alert)
                @php $s = $severityStyles[$alert['severity']]; @endphp
                <div class="relative overflow-hidden rounded-xl ring-1 transition-all duration-200 {{ $s['card'] }} p-4">
                    <div class="absolute inset-x-0 top-0 h-1 {{ $s['bar'] }}"></div>

                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center justify-center w-10 h-10 rounded-lg {{ $s['iconWrap'] }}">
                            <x-filament::icon :icon="$alert['icon']" class="w-5 h-5" />
                        </div>
                        @if ($alert['count'] > 0)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wide {{ $s['pill'] }}">
                                {{ $s['pillLabel'] }}
                            </span>
                        @endif
                    </div>

                    <div class="space-y-0.5">
                        <div class="text-sm font-medium text-slate-700 dark:text-slate-200 leading-tight">
                            {{ $alert['title'] }}
                        </div>
                        <div class="text-xs text-slate-500 dark:text-slate-400">
                            {{ $alert['subtitle'] }}
                        </div>
                    </div>

                    <div class="mt-3 flex items-end justify-between">
                        <div class="text-3xl font-bold tabular-nums leading-none {{ $alert['count'] > 0 ? $s['count'] : 'text-slate-400 dark:text-slate-500' }}">
                            {{ number_format($alert['count']) }}
                        </div>
                        @if ($alert['count'] === 0)
                            <div class="inline-flex items-center gap-1 text-xs text-emerald-600 dark:text-emerald-400 font-medium">
                                <x-filament::icon icon="heroicon-m-check-circle" class="w-4 h-4" />
                                {{ __('All clear') }}
                            </div>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
