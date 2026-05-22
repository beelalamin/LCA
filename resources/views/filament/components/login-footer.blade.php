@php
    $routeName = optional(request()->route())->getName() ?? '';
    $isLogin = str_contains($routeName, '.auth.')
        || str_ends_with($routeName, '.login')
        || str_contains(request()->path(), 'login');
@endphp

@if($isLogin)
    <div class="fi-login-footer w-full text-center pb-6 px-4">
        <p class="text-[11px] md:text-xs text-gray-400 dark:text-gray-500 tracking-wide">
            {{ __('Part of the ADDA product line · Crafted by Ideacraft Technology') }}
        </p>
    </div>
@endif
