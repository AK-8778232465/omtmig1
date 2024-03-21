<?php

namespace App\Http\Middleware;

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Auth\Middleware\Authenticate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;

class TokenAuthMiddleware extends Authenticate
{
    public function __construct(AuthFactory $auth)
    {
        parent::__construct($auth);
    }

    public function handle($request, Closure $next, ...$guards)
    {
        try {
            $this->authenticate($request, $guards);
        } catch (AuthenticationException $e) {
            // Handle authentication failure
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Perform any additional logic here if needed

        return $next($request);
    }
}
