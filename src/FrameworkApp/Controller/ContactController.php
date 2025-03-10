<?php

declare(strict_types=1);

namespace App\FrameworkApp\Controller;

use App\Framework\Controller\AbstractController;
use App\Framework\Http\Response;
use App\Framework\Http\ServerRequest;
use App\Framework\Security\SecurityMiddleware;
use App\Framework\View\TwigService;

class ContactController extends AbstractController
{
    public function __construct(TwigService $twig, SecurityMiddleware $security)
    {
        parent::__construct($twig, $security);
    }

    public function show(ServerRequest $request): Response
    {
        return $this->render('contact.html.twig');
    }
}
