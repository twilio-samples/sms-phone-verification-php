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

readonly final class CodeRequestFormPageHandler implements RequestHandlerInterface
{
    public function __construct(private TemplateRendererInterface $template)
    {
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ?FlashMessagesInterface $flashMessages */
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, null);

        /** @var array $formData */
        $formData = $flashMessages?->getFlash('form-data') ?? [];
        return new HtmlResponse(
            $this->template->render('app::code-request-form-page', [
                'error'     => $flashMessages?->getFlash('form-error') ?? "",
                'form_data' => $formData,
            ])
        );
    }
}