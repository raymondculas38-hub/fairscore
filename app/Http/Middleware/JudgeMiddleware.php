<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class JudgeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!auth()->check() || !auth()->user()->isJudge()) {
            return redirect()->route('judge.login')->with('error', 'Access denied. Judge credentials required.');
        }

        return $next($request);
    }
}
