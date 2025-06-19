<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // ユーザーがログインしているかチェック
        if (!$request->user()) {
            return response()->json([
                'message' => '認証が必要です'
            ], 401);
        }

        // 管理者権限をチェック
        if (!$request->user()->isAdmin()) {
            return response()->json([
                'message' => '管理者権限が必要です'
            ], 403);
        }

        return $next($request);
    }
}
