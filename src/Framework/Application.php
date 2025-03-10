<?php

declare(strict_types=1);

namespace App\Framework;

use App\Framework\Routing\Router;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use App\Framework\Routing\Exception\RouteNotFoundException;
use App\Framework\Http\Response;

/**
 * Main application class that handles the request/response lifecycle
 */
class Application
{
    private Router $router;

    public function __construct(Router $router)
    {
        $this->router = $router;
    }

    /**
     * Handle the HTTP request and generate a response
     *
     * @param ServerRequestInterface $request The incoming request
     * @return ResponseInterface The response
     */
    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        try {
            $route = $this->router->match($request);
            $result = $this->router->dispatch($route, $request);

            // If the result is already a response, return it
            if ($result instanceof ResponseInterface) {
                return $result;
            }

            // Otherwise, wrap it in a response
            return $this->wrapResponse($result);
        } catch (RouteNotFoundException $e) {
            return new Response(
                404,
                ['Content-Type' => 'text/html'],
                "404 Not Found: {$e->getMessage()}"
            );
        } catch (\Throwable $e) {
            // In a production environment, you'd want to log this error
            // and show a user-friendly message instead
            return new Response(
                500,
                ['Content-Type' => 'text/html'],
                "500 Internal Server Error: {$e->getMessage()}"
            );
        }
    }

    /**
     * Wrap a controller result in a Response if needed
     *
     * @param mixed $result The controller result
     * @return ResponseInterface The wrapped response
     */
    private function wrapResponse(mixed $result): ResponseInterface
    {
        // Handle string results (most common)
        if (is_string($result)) {
            return new Response(
                200,
                ['Content-Type' => 'text/html'],
                $result
            );
        }

        // Handle array/object results as JSON
        if (is_array($result) || is_object($result)) {
            return new Response(
                200,
                ['Content-Type' => 'application/json'],
                json_encode($result) ?: '{}'
            );
        }

        // Default empty response
        return new Response(
            204,
            [],
            null
        );
    }
}
