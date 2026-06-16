<?php

declare(strict_types=1);

namespace App\Handler;

use App\Repository\UserRepository;
use Mezzio\Template\TemplateRendererInterface;
use Psr\Container\ContainerInterface;

final class DashboardHandlerFactory
{
    public function __invoke(ContainerInterface $container): DashboardHandler
    {
        return new DashboardHandler(
            $container->get(TemplateRendererInterface::class),
            $container->get(UserRepository::class)
        );
    }
}
