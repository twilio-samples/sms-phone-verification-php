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
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

final class VerifyCheckHandler implements RequestHandlerInterface
{
    public function __construct(
        private readonly Client $client,
        private readonly string $verificationSid,
        private readonly UserRepository $userRepository
    ) {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);

        /** @var ?FlashMessagesInterface $flashMessages */
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        $userId = $session->get('user_id');
        if ($userId === null) {
            return new RedirectResponse('/');
        }

        $user = $this->userRepository->findById((int) $userId);
        if ($user === null) {
            return new RedirectResponse('/');
        }

        $data = $request->getParsedBody() ?? [];
        $code = trim($data['code'] ?? '');

        if (empty($code)) {
            $flashMessages?->flash('error', 'Please enter the verification code.');
            return new RedirectResponse('/verify');
        }

        try {
            $verificationCheck = $this->client
                ->verify
                ->v2
                ->services($this->verificationSid)
                ->verificationChecks
                ->create([
                    'code' => $code,
                    'to' => $user['phone_number'],
                ]);

            if ($verificationCheck->status === 'approved') {
                $this->userRepository->setVerified((int) $userId);
                return new RedirectResponse('/dashboard');
            }

            $flashMessages?->flash('error', 'Invalid verification code.');
        } catch (TwilioException $e) {
            $flashMessages?->flash('error', 'Verification failed: ' . $e->getMessage());
        }

        return new RedirectResponse('/verify');
    }
}
