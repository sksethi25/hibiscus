<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use \Illuminate\Http\Request;
use Closure;
use App\ApiResponse;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return route('login');
        }
    }

    /**
     * Function get called after oauth proccess a request.
     *
     * @param \Illuminate\Http\Request $request Request object.
     * @param \Closure                 $next    Closure.
     * @param string|null              $guard   Guaurd.
     * @param string                   $mode    Mode.
     *
     * @return \Illuminate\Http\JsonResponse Return data that user is authenticated or not.
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guard=isset($guards[0]) ? $guards[0]: 'web';
        $mode=isset($guards[1]) ? $guards[1]: 'full';

        $headers = $request->headers->all();
        if ($this->auth->guard($guard)->guest() === true) {


            if ($mode === 'full') {
                return ApiResponse::unauthorizedError(EC_UNAUTHORIZED, 'Authorization failed. Please login before this operation1.', 'Authorization');
            } else if ($mode === 'semi' && isset($headers['authorization']) === true && isset($headers['authorization'][0]) === true && empty($headers['authorization'][0]) === false) {
                return ApiResponse::unauthorizedError(EC_UNAUTHORIZED, 'Authorization failed. Please login before this operation2.', 'Authorization');
            }
        }
        return $next($request);
    }
}
