<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Psr\Container\ContainerInterface;

final class RegisterProcessHandlerFactory
{
    public function __invoke(ContainerInterface $container): RegisterProcessHandler
    {
        return new RegisterProcessHandler(
            $container->get(UserRepository::class)
        );
    }
}
