<?php

namespace App\Factory;

use Psr\Container\ContainerInterface;
use Twilio\Rest\Client;

class TwilioClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $config = $container->get('config')['twilio'];
        return new Client($config['account_sid'], $config['auth_token']);
    }
}