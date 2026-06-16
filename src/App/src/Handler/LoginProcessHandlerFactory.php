<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;

final class LoginProcessHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginProcessHandler
    {
        return new LoginProcessHandler(
            $container->get(UserRepository::class)
        );
    }
}
