<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CodeRequestProcessingHandler;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Session\SessionInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Twilio\Rest\Verify;
use Twilio\Rest\Verify\V2\Service\VerificationInstance;
use Twilio\Rest\Verify\V2\Service\VerificationList;
use Twilio\Rest\Verify\V2\ServiceContext;

class CodeRequestProcessingHandlerTest extends TestCase
{
    private FlashMessagesInterface&MockObject $flashMessages;
    private ServerRequestInterface&MockObject $request;
    private SessionInterface&MockObject $session;
    private Client&MockObject $client;
    private string $verificationSid;

    public function setUp(): void
    {
        $this->client          = $this->createMock(Client::class);
        $this->flashMessages   = $this->createMock(FlashMessagesInterface::class);
        $this->request         = $this->createMock(ServerRequestInterface::class);
        $this->session         = $this->createMock(SessionInterface::class);
        $this->verificationSid = "XXXXXXXXXXXXXXXXXXXXXXXXXXX";
    }

    public function testCanSendVerificationCodeIfRequestDataIsValid()
    {
        $phoneNumber = "+611234567890";

        $this->session
            ->expects($this->once())
            ->method('set')
            ->with("phone-number", $phoneNumber);

        $this->request
            ->expects($this->atMost(2))
            ->method('getAttribute')
            ->willReturn($this->flashMessages, $this->session);
        $this->request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                "username" => "username",
                "password" => "password12",
                "number"   => $phoneNumber,
            ]);

        $this->setupTwilioRestClient($phoneNumber);

        $handler  = new CodeRequestProcessingHandler($this->client, "XXXXXXXXXXXXXXXXXXXXXXXXXXX");
        $response = $handler->handle($this->request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame("/verify", $response->getHeaderLine('Location'));
    }

    #[TestWith(["username", "password", "+611234567890"])]
    #[TestWith(["user", "password123", "+611234567890"])]
    #[TestWith(["username", "password123", "widgets"])]
    public function testRedirectsToDefaultRouteIfRequestDataIsInvalid(
        string $username,
        string $password,
        string $phoneNumber
    ) {
        $this->request
            ->expects($this->atMost(2))
            ->method('getAttribute')
            ->willReturn($this->flashMessages, $this->session);
        $this->request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                "number"   => $phoneNumber,
                "password" => $password,
                "username" => $username,
            ]);

        $this->flashMessages
            ->expects($this->atMost(2))
            ->method("flash");

        $handler  = new CodeRequestProcessingHandler($this->client, "XXXXXXXXXXXXXXXXXXXXXXXXXXX");
        $response = $handler->handle($this->request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame("/", $response->getHeaderLine('Location'));
    }

    public function testRedirectsToDefaultRouteIfVerificationCodeCouldNotBeSent()
    {
        $this->request
            ->expects($this->atMost(2))
            ->method('getAttribute')
            ->willReturn($this->flashMessages, $this->session);
        $phoneNumber = "+611234567890";
        $this->request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                "number"   => $phoneNumber,
                "password" => "password12",
                "username" => "username",
            ]);
        $this->setupTwilioRestClient($phoneNumber, new TwilioException("Something went wrong"));

        $this->flashMessages
            ->expects($this->atMost(2))
            ->method("flash");

        $handler  = new CodeRequestProcessingHandler($this->client, "XXXXXXXXXXXXXXXXXXXXXXXXXXX");
        $response = $handler->handle($this->request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame("/", $response->getHeaderLine('Location'));
    }

    public function setupTwilioRestClient(
        string $phoneNumber,
        ?TwilioException $exception = null
    ): void {
        $verifications = $this->createMock(VerificationList::class);
        if ($exception === null) {
            $verifications
                ->expects($this->once())
                ->method("create")
                ->with($phoneNumber, "sms");
        } else {
            $verifications
                ->expects($this->once())
                ->method("create")
                ->with($phoneNumber, "sms")
                ->will($this->throwException($exception));
        }
        $serviceContext = $this->createMock(ServiceContext::class);
        $serviceContext
            ->expects($this->once())
            ->method('__get')
            ->with("verifications")
            ->willReturn($verifications);
        $v2 = $this->createMock(Verify\V2::class);
        $v2
            ->expects($this->once())
            ->method("__call")
            ->with("services", [$this->verificationSid])
            ->willReturn($serviceContext);
        $verify = $this->createMock(Verify::class);
        $verify
            ->expects($this->once())
            ->method('__get')
            ->with("v2")
            ->willReturn($v2);

        $this->client
            ->expects($this->once())
            ->method('__get')
            ->with("verify")
            ->willReturn($verify);
    }
}
