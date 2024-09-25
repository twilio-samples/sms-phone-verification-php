<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CodeRequestFormPageHandler;
use App\Handler\CodeRequestFormPageHandlerFactory;
use AppTest\InMemoryContainer;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;

class CodeRequestFormPageHandlerFactoryTest extends TestCase
{
    public function testFactory(): void
    {
        $container = new InMemoryContainer();
        $container->setService(TemplateRendererInterface::class, $this->createMock(TemplateRendererInterface::class));

        $factory = new CodeRequestFormPageHandlerFactory();
        $handler = $factory($container);

        self::assertInstanceOf(CodeRequestFormPageHandler::class, $handler);
    }
}
