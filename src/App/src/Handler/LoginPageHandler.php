<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LoginPageHandler implements RequestHandlerInterface
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
        if ($userId !== null) {
            $user = $this->userRepository->findById((int) $userId);
            if ($user !== null) {
                return new RedirectResponse($user['verified'] ? '/dashboard' : '/verify');
            }
        }

        /** @var ?FlashMessagesInterface $flashMessages */
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);
        $error = $flashMessages?->getFlash('error');

        return new HtmlResponse($this->template->render('app::login', [
            'error' => $error,
        ]));
    }
}
