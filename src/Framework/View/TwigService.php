<?php

declare(strict_types=1);

namespace App\Framework\View;

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use Twig\Loader\FilesystemLoader;
use Twig\Extension\DebugExtension;
use App\Framework\Security\UserInterface;

class TwigService
{
    private Environment $twig;

    /**
     * Create a new TwigService instance
     *
     * @param string $templatesPath Path to the templates directory
     * @param string $cachePath Path to the cache directory or false to disable caching
     * @param bool $debug Whether to enable debug mode
     */
    public function __construct(
        string $templatesPath,
        string $cachePath = '',
        bool $debug = false
    ) {
        $loader = new FilesystemLoader($templatesPath);

        $options = [
            'debug' => $debug,
            'strict_variables' => $debug
        ];

        // Enable cache in production
        if (!$debug && !empty($cachePath)) {
            $options['cache'] = $cachePath;
        }

        $this->twig = new Environment($loader, $options);

        // Add extensions
        if ($debug) {
            $this->twig->addExtension(new DebugExtension());
        }

        // Add core filters and functions
        $this->addCoreFunctionsAndFilters();
    }

    /**
     * Render a template with the given context
     *
     * @param string $template Template name
     * @param array $context Variables to pass to the template
     * @return string Rendered template
     */
    public function render(string $template, array $context = []): string
    {
        try {
            return $this->twig->render($template, $context);
        } catch (LoaderError | SyntaxError | RuntimeError $e) {
            return $e->getMessage();
        }
    }

    /**
     * Get the Twig environment
     *
     * @return Environment
     */
    public function getEnvironment(): Environment
    {
        return $this->twig;
    }

    /**
     * Add custom functions and filters to Twig
     */
    private function addCoreFunctionsAndFilters(): void
    {
        // Add a function to check if a user has a specific role
        $this->twig->addFunction(new \Twig\TwigFunction('is_granted', function ($role, ?UserInterface $user = null) {
            if ($user === null) {
                return false;
            }

            return in_array($role, $user->getRoles());
        }));

        // Add a function to generate CSRF tokens
        $this->twig->addFunction(new \Twig\TwigFunction('csrf_token', function () {
            // This is a placeholder. The actual implementation will be done later
            // once we have access to the SecurityMiddleware.
            if (isset($GLOBALS['securityMiddleware'])) {
                return $GLOBALS['securityMiddleware']->generateCsrfToken();
            }

            // Fallback for if the global isn't set
            if (function_exists('bin2hex') && function_exists('random_bytes')) {
                $token = bin2hex(random_bytes(32));
                $_SESSION['csrf_token'] = $token;
                return $token;
            }

            return '';
        }));
    }

    /**
     * Add the SecurityMiddleware to the service
     */
    public function setSecurityMiddleware($securityMiddleware): void
    {
        $GLOBALS['securityMiddleware'] = $securityMiddleware;

        // Add the actual csrf_token function now that we have the security middleware
        $this->twig->addFunction(new \Twig\TwigFunction('csrf_token', function () use ($securityMiddleware) {
            return $securityMiddleware->generateCsrfToken();
        }));
    }
}
