<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

abstract class Controller
{
    /**
     * Redirect back with existing query filters preserved, or fallback to specified route.
     */
    protected function redirectWithFilters(Request $request, string $fallbackRoute = 'projects.index', array $fallbackParams = []): RedirectResponse
    {
        $returnUrl = $request->input('return_url');
        if ($returnUrl && is_string($returnUrl)) {
            $parsed = parse_url($returnUrl);
            $path = $parsed['path'] ?? '';
            if (str_starts_with($path, '/projects') || (isset($parsed['host']) && in_array($parsed['host'], ['aitoolgroup.com', 'localhost', '127.0.0.1'], true))) {
                return redirect($returnUrl);
            }
        }

        $previous = url()->previous();
        if ($previous && $previous !== url()->current() && str_contains($previous, 'projects')) {
            return redirect($previous);
        }

        return to_route($fallbackRoute, $fallbackParams);
    }
}

