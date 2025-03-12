<?php

declare(strict_types=1);

use App\Framework\Security\AuthenticationInterface;
use App\Framework\Security\DatabaseAuthProvider;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\View\TwigService;
use Psr\Container\ContainerInterface;

use function DI\create;
use function DI\factory;
use function DI\get;
use function DI\env;

return [
    // Application parameters
    'app.debug' => env('APP_DEBUG', false),
    'app.environment' => env('APP_ENV', 'production'),

    // Database parameters
    'db.path' => env('DB_DATABASE', __DIR__ . '/../../../database/database.sqlite'),

    // Twig parameters
    'twig.templates_path' => __DIR__ . '/../Templates',
    'twig.cache_path' => __DIR__ . '/../../../var/cache/twig',

    // Security parameters
    'security.public_routes' => ['/login', '/logout'],
    'security.csrf_protection' => true,

    // Database connection
    PDO::class => factory(function (ContainerInterface $c) {
        $dbPath = $c->get('db.path');

        // Create database directory if it doesn't exist
        $dbDir = dirname($dbPath);
        if (!file_exists($dbDir)) {
            mkdir($dbDir, 0755, true);
        }

        // Ensure the database file exists
        if (!file_exists($dbPath)) {
            file_put_contents($dbPath, '');
        }

        $dsn = "sqlite:{$dbPath}";
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        return new PDO($dsn, null, null, $options);
    }),

    // Twig service
    TwigService::class => factory(function (ContainerInterface $c) {
        $templatesPath = $c->get('twig.templates_path');
        $cachePath = $c->get('twig.cache_path');

        // Create templates directory if it doesn't exist
        if (!file_exists($templatesPath)) {
            mkdir($templatesPath, 0755, true);
        }

        // Create cache directory if it doesn't exist
        if (!file_exists($cachePath)) {
            mkdir($cachePath, 0755, true);
        }

        // Debug mode based on environment
        $debug = $c->get('app.environment') !== 'production';

        return new TwigService($templatesPath, $cachePath, $debug);
    }),

    AuthenticationInterface::class => DI\create(DatabaseAuthProvider::class),

    SecurityMiddleware::class => factory(function (ContainerInterface $c) {
        return new SecurityMiddleware(
            $c->get(AuthenticationInterface::class),
            [
                'public_routes' => $c->get('security.public_routes'),
                'csrf_protection' => $c->get('security.csrf_protection'),
            ]
        );
    }),
];
