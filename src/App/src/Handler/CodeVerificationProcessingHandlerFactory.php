<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Twilio\Rest\Client;

class CodeVerificationProcessingHandlerFactory
{
    public function __invoke(ContainerInterface $container): CodeVerificationProcessingHandler
    {
        /** @var array $config */
        $config = $container->get("config")["twilio"];

        /** @var Client $client */
        $client = $container->get(Client::class);

        return new CodeVerificationProcessingHandler($client, (string) $config["verification_sid"]);
    }
}
