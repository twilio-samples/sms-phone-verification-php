<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

final class LoginPageHandlerFactory
{
    public function __invoke(ContainerInterface $container): LoginPageHandler
    {
        return new LoginPageHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(UserRepository::class)
        );
    }
}
