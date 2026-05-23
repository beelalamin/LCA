@php
    // brandLogo() is rendered in BOTH the sidebar header and the auth/login pages.
    // We can't rely on request()->route()/path() because Livewire updates (e.g.
    // the failed-login re-render) hit /livewire/update — that would flip the
    // brand block into the sidebar variant and wipe the description/logo from
    // the login page. Auth state is the reliable signal: unauthenticated = login.
    $isLogin = ! auth()->check();
@endphp

@if($isLogin)
    <div class="fi-login-brand flex flex-col items-center md:items-start text-center md:text-start gap-y-6 w-full">
        <picture class="block w-full max-w-md">
            <source srcset="{{ asset('images/adda-logo-dark.svg') }}" media="(prefers-color-scheme: dark)">
            <img src="{{ asset('images/adda-logo-dark.svg') }}"
                 alt="ADDA Inventory Hub"
                 class="w-full h-auto object-contain rounded-2xl shadow-lg ring-1 ring-black/5 dark:ring-white/10">
        </picture>

        <div class="space-y-3 max-w-md">
            <h1 class="text-3xl md:text-4xl font-bold tracking-tight text-amber-600 dark:text-amber-400">
                {{ __('Adda Inventory Hub') }}
            </h1>
            <div class="text-xs md:text-sm uppercase tracking-[0.25em] text-gray-500 dark:text-gray-400">
                {{ __('Asset Management Platform') }}
            </div>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 leading-relaxed">
                {{ __('An advanced platform for registering, tracking, auditing, and managing organizational assets across their full lifecycle — built for precision, security, and scale.') }}
            </p>
        </div>
    </div>
@else
    <div class="fi-brand-sidebar" aria-label="ADDA Inventory Hub">
        <div class="fi-brand-wordmark">
            <span class="fi-brand-wordmark-line">A<span class="fi-brand-wordmark-accent">DD</span>A</span>
            <span class="fi-brand-wordmark-sub">{{ __('Inventory Hub') }}</span>
        </div>
    </div>
@endif
