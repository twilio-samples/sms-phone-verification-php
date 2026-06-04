<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;
use Twilio\Rest\Client;

final class VerifySendHandlerFactory
{
    public function __invoke(ContainerInterface $container): VerifySendHandler
    {
        /** @var array $config */
        $config = $container->get('config');

        return new VerifySendHandler(
            $container->get(Client::class),
            $config['twilio']['verification_sid'] ?? '',
            $container->get(UserRepository::class)
        );
    }
}
