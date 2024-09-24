<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CodeVerificationFormPageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;

class CodeVerificationFormPageHandlerTest extends TestCase
{
    private TemplateRendererInterface&MockObject $renderer;

    private FlashMessagesInterface&MockObject $flashMessages;

    private ServerRequestInterface&MockObject $request;

    private SessionInterface&MockObject $session;

    public function setUp(): void
    {
        $this->renderer      = $this->createMock(TemplateRendererInterface::class);
        $this->request       = $this->createMock(ServerRequestInterface::class);
        $this->flashMessages = $this->createMock(FlashMessagesInterface::class);
        $this->session       = $this->createMock(SessionInterface::class);
    }

    public function testReturnsRedirectResponseWhenPhoneNumberIsNotSetInTheSession(): void
    {
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with("phone-number")
            ->willReturn(null);

        $this->request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(SessionMiddleware::SESSION_ATTRIBUTE)
            ->willReturn($this->session);

        $handler  = new CodeVerificationFormPageHandler($this->renderer);
        $response = $handler->handle($this->request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame("/", $response->getHeaderLine('Location'));
    }

    public function testReturnsHtmlResponseWhenPhoneNumberIsSetInTheSession(): void
    {
        $this->renderer
            ->expects($this->once())
            ->method('render')
            ->with('app::code-verification-form-page', $this->isType('array'))
            ->willReturn('');

        $phoneNumber = "+610409123456";

        $this->session
            ->expects($this->once())
            ->method('get')
            ->with("phone-number", null)
            ->willReturn($phoneNumber);

        $this->flashMessages
            ->expects($this->atMost(2))
            ->method('getFlash')
            ->willReturnOnConsecutiveCalls("", "");

        $this->request
            ->expects($this->atMost(2))
            ->method('getAttribute')
            ->willReturnOnConsecutiveCalls($this->session, $this->flashMessages);

        $handler  = new CodeVerificationFormPageHandler($this->renderer);
        $response = $handler->handle($this->request);

        self::assertInstanceOf(HtmlResponse::class, $response);
    }
}
