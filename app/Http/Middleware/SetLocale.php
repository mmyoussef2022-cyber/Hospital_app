<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        // Check if language is provided in URL parameter
        if ($request->has('lang')) {
            $locale = $request->get('lang');
            if (in_array($locale, ['ar', 'en'])) {
                Session::put('locale', $locale);
                App::setLocale($locale);
            }
        } 
        // Check if language is stored in session
        elseif (Session::has('locale')) {
            $locale = Session::get('locale');
            if (in_array($locale, ['ar', 'en'])) {
                App::setLocale($locale);
            }
        }
        // Default to Arabic and ensure session is set
        else {
            App::setLocale('ar');
            Session::put('locale', 'ar');
        }

        // Debug: Log current locale and session
        \Log::info('SetLocale Middleware - Current locale: ' . App::getLocale() . ', Session locale: ' . Session::get('locale'));

        return $next($request);
    }
}