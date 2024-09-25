<?php

declare(strict_types=1);

namespace AppTest\Handler;

use App\Handler\CodeVerificationProcessingHandler;
use Laminas\Diactoros\Response\RedirectResponse;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Mezzio\Template\TemplateRendererInterface;
use PHPUnit\Framework\Attributes\TestWith;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;
use Twilio\Rest\Verify;
use Twilio\Rest\Verify\V2\Service\VerificationCheckInstance;
use Twilio\Rest\Verify\V2\Service\VerificationCheckList;
use Twilio\Rest\Verify\V2\ServiceContext;

class CodeVerificationProcessingHandlerTest extends TestCase
{
    private TemplateRendererInterface&MockObject $renderer;

    private FlashMessagesInterface&MockObject $flashMessages;

    private ServerRequestInterface&MockObject $request;

    private SessionInterface&MockObject $session;
    private Client&MockObject $client;
    private string $verificationSid;

    public function setUp(): void
    {
        $this->client          = $this->createMock(Client::class);
        $this->flashMessages   = $this->createMock(FlashMessagesInterface::class);
        $this->renderer        = $this->createMock(TemplateRendererInterface::class);
        $this->request         = $this->createMock(ServerRequestInterface::class);
        $this->session         = $this->createMock(SessionInterface::class);
        $this->verificationSid = "XXXXXXXXXXXXXXXXXXXXXXXXXXX";
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

        $handler  = new CodeVerificationProcessingHandler($this->client, $this->verificationSid);
        $response = $handler->handle($this->request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame("/", $response->getHeaderLine('Location'));
    }

    #[TestWith(['approved', "message-success", "Verification check succeeded.", false])]
    #[TestWith(['pending', "form-error", "Verification check failed. Reason: pending", false])]
    #[TestWith(['canceled', "form-error", "Verification check failed. Reason: canceled", false])]
    #[TestWith([
        'max_attempts_reached',
        "form-error",
        "Verification check failed. Reason: max_attempts_reached",
        false,
    ])]
    #[TestWith(['deleted', "form-error", "Verification check failed. Reason: deleted", false])]
    #[TestWith(['failed', "form-error", "Verification check failed. Reason: failed", false])]
    #[TestWith(['expired', "form-error", "Verification check failed. Reason: expired", false])]
    #[TestWith(['expired', "form-error", "Verification check failed. Reason: Unable to create record", true])]
    public function testRedirectsToTheVerifyRouteAfterSettingTheCorrectFlashMessage(
        string $status,
        string $flashKey,
        string $flashValue,
        bool $throwsException
    ) {
        $code        = "XXXXXXXXXXXXXXXXXXXXXXXXXXX";
        $phoneNumber = "+610409123456";

        $this->session
            ->expects($this->once())
            ->method('get')
            ->with("phone-number")
            ->willReturn($phoneNumber);

        $verificationCheckInstance = $this->createMock(VerificationCheckInstance::class);
        $verificationCheckInstance
            ->expects($throwsException ? $this->never() : $this->any())
            ->method('__get')
            ->with("status")
            ->willReturn($status);

        $this->setupTwilioRestClient(
            $code,
            $phoneNumber,
            $verificationCheckInstance,
            $throwsException ? new TwilioException("Unable to create record") : null
        );

        $this->flashMessages
            ->expects($this->once())
            ->method("flash")
            ->with($flashKey, $flashValue);

        $this->request
            ->expects($this->atMost(2))
            ->method('getAttribute')
            ->willReturn($this->session, $this->flashMessages);
        $this->request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn([
                "code" => $code,
            ]);

        $handler  = new CodeVerificationProcessingHandler($this->client, "XXXXXXXXXXXXXXXXXXXXXXXXXXX");
        $response = $handler->handle($this->request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame("/verify", $response->getHeaderLine('Location'));
    }

    #[TestWith([[]])]
    #[TestWith([["code" => ""]])]
    public function testRedirectsToVerifyRouteIfCodeIsInvalid(array $parsedBody): void
    {
        $phoneNumber = "+610409123456";
        $this->session
            ->expects($this->once())
            ->method('get')
            ->with("phone-number")
            ->willReturn($phoneNumber);

        $this->flashMessages
            ->expects($this->once())
            ->method("flash")
            ->with("form-error", "Verification check failed: Invalid code.");

        $this->request
            ->expects($this->atMost(2))
            ->method('getAttribute')
            ->willReturn($this->session, $this->flashMessages);
        $this->request
            ->expects($this->once())
            ->method("getParsedBody")
            ->willReturn($parsedBody);

        $handler  = new CodeVerificationProcessingHandler($this->client, "XXXXXXXXXXXXXXXXXXXXXXXXXXX");
        $response = $handler->handle($this->request);

        self::assertInstanceOf(RedirectResponse::class, $response);
        self::assertSame(302, $response->getStatusCode());
        self::assertSame("/verify", $response->getHeaderLine('Location'));
    }

    public function setupTwilioRestClient(
        string $code,
        string $phoneNumber,
        VerificationCheckInstance $verificationCheckInstance,
        ?TwilioException $exception = null
    ): void {
        $verificationChecks = $this->createMock(VerificationCheckList::class);
        if ($exception === null) {
            $verificationChecks
                ->expects($this->once())
                ->method("create")
                ->with([
                    "code" => $code,
                    "to"   => $phoneNumber,
                ])
                ->willReturn($verificationCheckInstance);
        } else {
            $verificationChecks
                ->expects($this->once())
                ->method("create")
                ->with([
                    "code" => $code,
                    "to"   => $phoneNumber,
                ])
                ->will($this->throwException($exception));
        }
        $serviceContext = $this->createMock(ServiceContext::class);
        $serviceContext
            ->expects($this->once())
            ->method('__get')
            ->with("verificationChecks")
            ->willReturn($verificationChecks);
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
