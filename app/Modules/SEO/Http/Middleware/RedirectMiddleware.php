<?php

namespace App\Modules\SEO\Http\Middleware;

use App\Modules\SEO\Models\Redirect;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RedirectMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $path = '/'.trim($request->path(), '/');
        $hash = hash('sha256', $path);

        $redirect = Redirect::where('from_path_hash', $hash)
            ->where('is_active', true)
            ->first();

        if ($redirect) {
            return redirect($redirect->to_path, $redirect->status_code);
        }

        return $next($request);
    }
}
