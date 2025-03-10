<?php

declare(strict_types=1);

use App\FrameworkApp\Controller\HomeController;
use App\FrameworkApp\Controller\ApiController;
use App\FrameworkApp\Controller\ContactController;

/**
 * Route Configuration
 *
 * Format: [
 *   [HTTP_METHOD, URI_PATH, CONTROLLER_CLASS, ACTION_METHOD],
 *   ...
 * ]
 */

return [
    ['GET', '/', HomeController::class, 'index'],
    ['GET', '/api/status', ApiController::class, 'status'],
    ['POST', '/api/status', ApiController::class, 'update'],
    ['GET', '/contact', ContactController::class, 'show'],

    // Add more routes as needed
    // ['GET', '/about', AboutController::class, 'index'],
];
