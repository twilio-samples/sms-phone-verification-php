<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CodeRequestProcessingHandler;
use App\Handler\CodeRequestProcessingHandlerFactory;
use AppTest\InMemoryContainer;
use PHPUnit\Framework\TestCase;
use Twilio\Rest\Client;

class CodeRequestProcessingHandlerFactoryTest extends TestCase
{
    public function testCanInstantiateTheHandler(): void
    {
        $container = new InMemoryContainer();
        $container->setService("config", [
            "twilio" => [
                "account_sid"      => "ACXXXXXXXXXXXXXXXXXXXXXXXXXXX",
                "auth_token"       => "XXXXXXXXXXXXXXXXXXXXXXXXXXX",
                "verification_sid" => "XXXXXXXXXXXXXXXXXXXXXXXXXXX",
            ],
        ]);
        $container->setService(Client::class, $this->createMock(Client::class));

        $handler = (new CodeRequestProcessingHandlerFactory())->__invoke($container);

        self::assertInstanceOf(CodeRequestProcessingHandler::class, $handler);
    }
}
