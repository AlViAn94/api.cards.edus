<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful as Middleware;

class SanctumCustom extends Middleware
{
    /**
     * Get the token for the current request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function getToken(Request $request)
    {
        if (!$request->header('X-Auth-Token')) {
        return route('login');
    }
        return $request->header('X-Auth-Token');
    }
}
