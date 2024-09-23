<?php

declare(strict_types=1);

namespace App\Handler;

use Psr\Container\ContainerInterface;
use Twilio\Rest\Client;

class CodeRequestProcessingHandlerFactory
{
    public function __invoke(ContainerInterface $container): CodeRequestProcessingHandler
    {
        $config = (array) $container->get("config")["twilio"];

        /** @var Client $client */
        $client = $container->get(Client::class);

        return new CodeRequestProcessingHandler($client, (string) $config["verification_sid"]);
    }
}
