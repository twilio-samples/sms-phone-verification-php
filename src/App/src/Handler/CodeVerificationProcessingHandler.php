<?php

declare(strict_types=1);

namespace App\Handler;

use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\NotEmpty;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

readonly final class CodeVerificationProcessingHandler implements RequestHandlerInterface
{
    private InputFilter $inputFilter;

    public function __construct(private Client $client, private string $verificationSid)
    {
        $code = new Input("code");
        $code->setRequired(true);
        $code->getFilterChain()
            ->attach(new StringTrim())
            ->attach(new StripTags());
        $code->getValidatorChain()
            ->attach(new NotEmpty());

        $this->inputFilter = new InputFilter();
        $this->inputFilter
            ->add($code);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ?FlashMessagesInterface $flashMessages */
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE, null);

        $this->inputFilter->setData($request->getParsedBody() ?? []);
        if (! $this->inputFilter->isValid()) {
            $flashMessages?->flash("form-error", "Verification check failed: Invalid code.");
            return new RedirectResponse("/verify");
        }

        /** @var string $phoneNumber */
        $phoneNumber = $flashMessages?->getFlash('phone-number') ?? "";

        try {
            $verificationCheck = $this->client
                ->verify
                ->v2
                ->services($this->verificationSid)
                ->verificationChecks
                ->create([
                    "code" => (string) $this->inputFilter->getValue("code"),
                    "to"   => $phoneNumber,
                ]);
            if ($verificationCheck->status === "approved") {
                $flashMessages?->flash("message-success", "Verification check succeeded.");
            } else {
                $flashMessages?->flash("form-error", "Verification check failed. Reason: {$verificationCheck->status}");
            }
        } catch (TwilioException $e) {
            $flashMessages?->flash("form-error", "Verification check failed. Reason: {$e->getMessage()}");
        }

        return new RedirectResponse("/verify");
    }
}