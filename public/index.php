<?php

declare(strict_types=1);

use DI\ContainerBuilder;
use App\Framework\Application;
use App\Framework\Routing\Router;
use App\Framework\Http\ServerRequest;

require_once __DIR__ . '/../vendor/autoload.php';


$request = ServerRequest::fromGlobals();

$containerBuilder = new ContainerBuilder();

$containerBuilder->enableCompilation(__DIR__ . '/../var/cache');

$container = $containerBuilder->build();

// Configure routes from configuration file
$router = $container->get(Router::class);
$routes = require __DIR__ . '/../src/FrameworkApp/config/routes.php';


foreach ($routes as [$method, $path, $controller, $action]) {
    $router->add($method, $path, [
        'controller' => $controller,
        'action' => $action
    ]);
}

$app = new Application($router);

$response = $app->handle($request);


http_response_code($response->getStatusCode());

foreach ($response->getHeaders() as $name => $values) {
    foreach ($values as $value) {
        header("{$name}: {$value}", false);
    }
}

echo $response->getBody();
