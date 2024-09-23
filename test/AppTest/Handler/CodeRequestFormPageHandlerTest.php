<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CodeRequestFormPageHandler;
use Laminas\Diactoros\Response\HtmlResponse;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;

class CodeRequestFormPageHandlerTest extends TestCase
{
    /** @var ContainerInterface&MockObject */
    protected $container;

    protected function setUp(): void
    {
        $this->container = $this->createMock(ContainerInterface::class);
    }

    public function testReturnsHtmlResponseWhenTemplateRendererProvided(): void
    {
        $renderer = $this->createMock(TemplateRendererInterface::class);
        $renderer
            ->expects($this->once())
            ->method('render')
            ->with('app::code-request-form-page', $this->isType('array'))
            ->willReturn('');

        $flashMessages = $this->createMock(FlashMessagesInterface::class);
        $flashMessages
            ->expects($this->atMost(2))
            ->method('getFlash')
            ->willReturn([]);

        $request = $this->createMock(ServerRequestInterface::class);
        $request
            ->expects($this->once())
            ->method('getAttribute')
            ->with(FlashMessageMiddleware::FLASH_ATTRIBUTE, null)
            ->willReturn($flashMessages);

        $homePage = new CodeRequestFormPageHandler($renderer);

        $response = $homePage->handle($request);

        self::assertInstanceOf(HtmlResponse::class, $response);
    }
}
