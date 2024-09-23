<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CodeVerificationProcessingHandler;
use App\Handler\CodeVerificationProcessingHandlerFactory;
use AppTest\InMemoryContainer;
use PHPUnit\Framework\TestCase;
use Twilio\Rest\Client;

class CodeVerificationProcessingHandlerFactoryTest extends TestCase
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

        $handler = (new CodeVerificationProcessingHandlerFactory())->__invoke($container);

        self::assertInstanceOf(CodeVerificationProcessingHandler::class, $handler);
    }
}
