<?php

namespace Laratrust\Middleware;

use Closure;

class LaratrustGroup extends LaratrustMiddleware
{
    /**
     * Handle incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  Closure $next
     * @param  string  $groups
     * @param  string|null  $team
     * @param  string|null  $options
     * @return mixed
     */
    public function handle($request, Closure $next, $groups, $team = null, $options = '')
    {
        if (!$this->authorization('groups', $groups, $team, $options)) {
            return $this->unauthorized();
        }

        return $next($request);
    }
}
