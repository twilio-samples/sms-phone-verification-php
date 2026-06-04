<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Laminas\Diactoros\Response\RedirectResponse;
use Laminas\Filter\StringTrim;
use Laminas\Filter\StripTags;
use Laminas\InputFilter\Input;
use Laminas\InputFilter\InputFilter;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\Regex;
use Laminas\Validator\StringLength;
use Mezzio\Flash\FlashMessageMiddleware;
use Mezzio\Flash\FlashMessagesInterface;
use Mezzio\Session\SessionInterface;
use Mezzio\Session\SessionMiddleware;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

final class RegisterProcessHandler implements RequestHandlerInterface
{
    private InputFilter $inputFilter;

    public function __construct(private readonly UserRepository $userRepository)
    {
        $username = new Input("username");
        $username->setRequired(true);
        $username->getFilterChain()
            ->attach(new StringTrim())
            ->attach(new StripTags());
        $username->getValidatorChain()
            ->attach(new NotEmpty())
            ->attach(new StringLength(["min" => 5, "max" => 255]));

        $password = new Input("password");
        $password->setRequired(true);
        $password->getFilterChain()
            ->attach(new StringTrim())
            ->attach(new StripTags());
        $password->getValidatorChain()
            ->attach(new NotEmpty())
            ->attach(new StringLength(['min' => 10]));

        $number = new Input("number");
        $number->setRequired(true);
        $number->getFilterChain()
            ->attach(new StringTrim())
            ->attach(new StripTags());
        $number->getValidatorChain()
            ->attach(new NotEmpty())
            ->attach(new Regex("/^\+[1-9]\d{1,14}$/"));

        $this->inputFilter = new InputFilter();
        $this->inputFilter
            ->add($username)
            ->add($password)
            ->add($number);
    }

    public function handle(ServerRequestInterface $request): ResponseInterface
    {
        /** @var ?FlashMessagesInterface $flashMessages */
        $flashMessages = $request->getAttribute(FlashMessageMiddleware::FLASH_ATTRIBUTE);

        $this->inputFilter->setData($request->getParsedBody() ?? []);

        if (!$this->inputFilter->isValid()) {
            $flashMessages?->flash('form-errors', $this->inputFilter->getMessages());
            $flashMessages?->flash('form-data', $this->inputFilter->getValues());
            return new RedirectResponse('/register');
        }

        $username = (string) $this->inputFilter->getValue('username');
        $password = (string) $this->inputFilter->getValue('password');
        $phoneNumber = (string) $this->inputFilter->getValue('number');

        $existingUser = $this->userRepository->findByUsername($username);
        if ($existingUser !== null) {
            $flashMessages?->flash('form-errors', ['username' => ['Username already exists']]);
            $flashMessages?->flash('form-data', $this->inputFilter->getValues());
            return new RedirectResponse('/register');
        }

        $userId = $this->userRepository->create($username, $password, $phoneNumber);

        /** @var SessionInterface $session */
        $session = $request->getAttribute(SessionMiddleware::SESSION_ATTRIBUTE);
        $session->set('user_id', $userId);

        return new RedirectResponse('/verify');
    }
}
