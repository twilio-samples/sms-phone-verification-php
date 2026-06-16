<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class DashboardHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly TemplateRendererInterface $template,
        private readonly UserRepository $userRepository
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        $userId = $session->get('user_id');
        if ($userId === null) {
            return new RedirectResponse('/');
        }

        $user = $this->userRepository->findById((int) $userId);
        if ($user === null) {
            return new RedirectResponse('/');
        }

        if (!$user['verified']) {
            return new RedirectResponse('/verify');
        }

        return new HtmlResponse($this->template->render('app::dashboard', [
            'user' => $user,
        ]));
    }
}
