<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class LoginProcessHandler implements RequestHandlerInterface
{
    public function __construct(private readonly UserRepository $userRepository)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = $request->getParsedBody() ?? [];
        $username = trim($data['username'] ?? '');
        $password = $data['password'] ?? '';

        /** @var ?FlashMessagesInterface $flashMessages */
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        $user = $this->userRepository->findByUsername($username);

        if ($user === null || !$this->userRepository->verifyPassword($password, $user['password'])) {
            $flashMessages?->flash('error', 'Invalid username or password.');
            return new RedirectResponse('/');
        }

        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->set('user_id', $user['id']);

        return new RedirectResponse($user['verified'] ? '/dashboard' : '/verify');
    }
}
