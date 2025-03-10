<?php

declare(strict_types=1);

use App\FrameworkApp\Controller\AdminController;
use App\FrameworkApp\Controller\AuthController;
use App\FrameworkApp\Controller\HomeController;
use App\FrameworkApp\Controller\ApiController;
use App\FrameworkApp\Controller\ContactController;

return [
    // Public routes
    ['GET', '/login', AuthController::class, 'loginForm'],
    ['POST', '/login', AuthController::class, 'login'],
    ['GET', '/logout', AuthController::class, 'logout'],

    // Secured routes
    ['GET', '/', HomeController::class, 'index'],
    ['GET', '/contact', ContactController::class, 'show'],
    ['GET', '/api/status', ApiController::class, 'status'],
    ['POST', '/api/status', ApiController::class, 'update'],

    // Admin-only routes
    ['GET', '/admin', AdminController::class, 'dashboard'],
    ['POST', '/admin/action', AdminController::class, 'action'],
];
