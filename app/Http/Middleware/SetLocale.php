<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $this->resolveLocale($request);

        if ($locale) {
            app()->setLocale($locale);
            session(['locale' => $locale]);
        }

        return $next($request);
    }

    protected function resolveLocale(Request $request): ?string
    {
        $supported = ['en', 'ar'];

        if (session()->has('locale') && in_array(session('locale'), $supported, true)) {
            return session('locale');
        }

        $user = $request->user();
        if ($user && in_array($user->preferred_locale, $supported, true)) {
            return $user->preferred_locale;
        }

        return null;
    }
}
