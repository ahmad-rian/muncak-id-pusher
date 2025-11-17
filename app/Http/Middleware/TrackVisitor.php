<?php

namespace App\Http\Middleware;

use App\Models\Visitor;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class TrackVisitor
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        Visitor::updateOrCreate([
            'ip_address' => $request->ip(),
            'date'       => Carbon::today()->format('Y-m-d'),
        ], [
            'path'       => $request->path(),
            'user_agent' => $request->userAgent(),
            'referrer'   => $request->headers->get('referer')
        ]);

        return $next($request);
    }
}
