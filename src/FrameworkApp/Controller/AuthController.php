<?php

declare(strict_types=1);

namespace App\FrameworkApp\Controller;

use App\Framework\Controller\AbstractController;
use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Security\AuthenticationInterface;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\View\TwigService;

class AuthController extends AbstractController
{
    private AuthenticationInterface $auth;

    public function __construct(
        AuthenticationInterface $auth,
        TwigService $twig,
        SecurityMiddleware $security
    ) {
        parent::__construct($twig, $security);
        $this->auth = $auth;
    }

    public function loginForm(ServerRequest $request): Response
    {
        // Check if there's an error message
        $error = $request->getAttribute('error', '');

        return $this->render('login.html.twig', [
            'error' => $error
        ]);
    }

    public function login(ServerRequest $request): Response
    {
        $data = $request->getParsedBody() ?? [];

        // Validate CSRF token
        if (!isset($data['csrf_token']) || !$this->security->validateCsrfToken($data['csrf_token'])) {
            return $this->redirectWithError('/login', 'Invalid CSRF token');
        }

        // Get username and password
        $username = $data['username'] ?? '';
        $password = $data['password'] ?? '';

        // Authenticate user
        $user = $this->auth->authenticate($username, $password);

        if ($user === null) {
            return $this->redirectWithError('/login', 'Invalid username or password');
        }

        // Successful login, redirect to home page
        return $this->redirect('/');
    }

    public function logout(ServerRequest $request): Response
    {
        $this->auth->logout();

        return $this->redirect('/login');
    }

    private function redirectWithError(string $path, string $error): Response
    {
        $_SESSION['login_error'] = $error;

        return $this->redirect($path);
    }
}
