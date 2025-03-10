<?php

namespace App\Framework\Routing\Exception;

use Throwable;

class RouteNotFoundException extends \Exception
{
    public function __construct($message = "Route not found", $code = 0, Throwable $previous = null)
    {
        parent::__construct($message, $code, $previous);
    }
}
