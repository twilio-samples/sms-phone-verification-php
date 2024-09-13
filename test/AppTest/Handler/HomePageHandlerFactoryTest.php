<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CodeRequestFormPageHandler;
use App\Handler\CodeRequestFormPageHandlerFactory;
use AppTest\InMemoryContainer;
use Mezzio\Router\RouterInterface;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\TestCase;

class HomePageHandlerFactoryTest extends TestCase
{
    public function testFactoryWithoutTemplate(): void
    {
        $container = new InMemoryContainer();
        $container->setService(RouterInterface::class, $this->createMock(RouterInterface::class));

        $factory  = new CodeRequestFormPageHandlerFactory();
        $homePage = $factory($container);

        self::assertInstanceOf(CodeRequestFormPageHandler::class, $homePage);
    }

    public function testFactoryWithTemplate(): void
    {
        $container = new InMemoryContainer();
        $container->setService(RouterInterface::class, $this->createMock(RouterInterface::class));
        $container->setService(TemplateRendererInterface::class, $this->createMock(TemplateRendererInterface::class));

        $factory  = new CodeRequestFormPageHandlerFactory();
        $homePage = $factory($container);

        self::assertInstanceOf(CodeRequestFormPageHandler::class, $homePage);
    }
}