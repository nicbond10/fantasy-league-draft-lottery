<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Auth\Middleware\RequirePassword;
use Symfony\Component\HttpFoundation\Response;

final class RequirePasswordConfirmation extends RequirePassword
{
    public function handle($request, Closure $next, $redirectToRoute = null, $passwordTimeoutSeconds = null)
    {
        $response = parent::handle($request, $next, $redirectToRoute, $passwordTimeoutSeconds);

        // Livewire's persistent middleware pipeline only stops automatically for
        // redirects. Abort with the JSON response before a component can mutate.
        if ($response instanceof Response && $response->getStatusCode() === Response::HTTP_LOCKED) {
            abort($response);
        }

        return $response;
    }
}
