<?php

declare(strict_types=1);

namespace App\Framework\Controller;

use App\Framework\Http\Response;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\View\TwigService;
use Psr\Http\Message\ServerRequestInterface;

/**
 * Base controller class with Twig rendering support
 */
abstract class AbstractController
{
    protected TwigService $twig;
    protected SecurityMiddleware $security;

    public function __construct(TwigService $twig, SecurityMiddleware $security)
    {
        $this->twig = $twig;
        $this->security = $security;
        $this->twig->setSecurityMiddleware($security);
    }

    /**
     * Render a template and create a response
     *
     * @param string $template Template name
     * @param array $context Variables to pass to the template
     * @param int $status HTTP status code
     * @param array $headers HTTP headers
     * @return Response
     */
    protected function render(
        string $template,
        array $context = [],
        int $status = 200,
        array $headers = []
    ): Response {
        // Add default context variables
        $context = array_merge([
            'request_uri' => $_SERVER['REQUEST_URI'] ?? '/',
        ], $context);

        // Get the current user from the request if available
        if (isset($GLOBALS['request']) && $GLOBALS['request'] instanceof ServerRequestInterface) {
            $context['user'] = $GLOBALS['request']->getAttribute('user');
        }

        // Render the template
        $content = $this->twig->render($template, $context);

        // Set default Content-Type if not provided
        $headers = array_merge([
            'Content-Type' => 'text/html; charset=UTF-8'
        ], $headers);

        return new Response($status, $headers, $content);
    }

    /**
     * Create a JSON response
     *
     * @param mixed $data Data to encode as JSON
     * @param int $status HTTP status code
     * @param array $headers HTTP headers
     * @return Response
     */
    protected function json($data, int $status = 200, array $headers = []): Response
    {
        $headers = array_merge([
            'Content-Type' => 'application/json'
        ], $headers);

        return new Response(
            $status,
            $headers,
            json_encode($data) ?: '{}'
        );
    }

    /**
     * Create a redirect response
     *
     * @param string $url URL to redirect to
     * @param int $status HTTP status code
     * @return Response
     */
    protected function redirect(string $url, int $status = 302): Response
    {
        return new Response(
            $status,
            ['Location' => $url],
            ''
        );
    }
}
