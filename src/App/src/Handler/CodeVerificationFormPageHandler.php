<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class CodeVerificationFormPageHandler implements RequestHandlerInterface
{
    public function __construct(private TemplateRendererInterface $template)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        $data = [];

        /** @var ?FlashMessagesInterface $flashMessages */
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, null);

        /** @var ?string $successMessage */
        $successMessage = $flashMessages?->getFlash('message-success');

        if ($successMessage !== null) {
            $data['success'] = $successMessage;
        }

        /** @var ?string $formError */
        $formError = $flashMessages?->getFlash('form-error');

        if ($formError !== null) {
            $data['form_error'] = $formError;
        }

        return new HtmlResponse($this->template->render('app::code-verification-form-page', $data));
    }
}