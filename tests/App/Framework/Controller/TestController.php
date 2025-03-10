<?php

namespace Tests\App\Framework\Controller;

use App\Framework\Controller\AbstractController;
use App\Framework\Http\Response;

/**
 * A concrete implementation of AbstractController for testing
 */
class TestController extends AbstractController
{
    public function testRender(string $template, array $context = [], int $status = 200, array $headers = []): Response
    {
        return $this->render($template, $context, $status, $headers);
    }

    public function testJson($data, int $status = 200, array $headers = []): Response
    {
        return $this->json($data, $status, $headers);
    }

    public function testRedirect(string $url, int $status = 302): Response
    {
        return $this->redirect($url, $status);
    }
}
