<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckIsCustomer
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user || !$user->is_active || !$user->customer) {
            return response()->json([
                'success' => false,
                'message' => 'Quyền truy cập bị từ chối. Chỉ dành cho Khách hàng.'
            ], Response::HTTP_FORBIDDEN);
        }

        return $next($request);
    }
}