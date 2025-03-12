<?php

declare(strict_types=1);

namespace App\FrameworkApp\Controller;

use App\Framework\Controller\AbstractController;
use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Security\AuthenticationInterface;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\View\TwigService;

class AdminController extends AbstractController
{
    public function __construct(
        TwigService $twig,
        SecurityMiddleware $security
    ) {
        parent::__construct($twig, $security);
    }

    public function dashboard(ServerRequest $request): Response
    {
        // Verify user has admin role
        if (!$this->security->verifyAuthorization($request, ['ROLE_ADMIN'])) {
            return $this->render('error.html.twig', [
                'status' => 403,
                'title' => 'Access Denied',
                'message' => 'You do not have permission to access this page.'
            ], 403);
        }

        // Sample user data for the demo
        $demoUsers = [
            ['username' => 'admin', 'roles' => ['ROLE_ADMIN', 'ROLE_USER']],
            ['username' => 'user', 'roles' => ['ROLE_USER']],
            ['username' => 'editor', 'roles' => ['ROLE_EDITOR', 'ROLE_USER']]
        ];

        // Get server software for display
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'Unknown';

        return $this->render('admin.html.twig', [
            'demo_users' => $demoUsers,
            'server_software' => $serverSoftware
        ]);
    }

    public function action(ServerRequest $request): Response
    {
        // Verify user has admin role
        if (!$this->security->verifyAuthorization($request, ['ROLE_ADMIN'])) {
            return $this->render('error.html.twig', [
                'status' => 403,
                'title' => 'Access Denied',
                'message' => 'You do not have permission to perform this action.'
            ], 403);
        }

        $data = $request->getParsedBody() ?? [];

        // Validate CSRF token
        if (!isset($data['csrf_token']) || !$this->security->validateCsrfToken($data['csrf_token'])) {
            return $this->render('error.html.twig', [
                'status' => 400,
                'title' => 'Bad Request',
                'message' => 'Invalid CSRF token.'
            ], 400);
        }

        // Process the action (just an example)
        $action = $data['action'] ?? '';
        $result = "Action '{$this->security->escapeOutput($action)}' completed successfully.";

        return $this->render('error.html.twig', [
            'status' => 200,
            'title' => 'Action Result',
            'message' => $result
        ]);
    }
}
