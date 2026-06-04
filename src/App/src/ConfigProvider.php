<?php

declare(strict_types=1);

namespace App;

use App\Factory\TwilioClientFactory;
use App\Handler\DashboardHandler;
use App\Handler\DashboardHandlerFactory;
use App\Handler\LoginPageHandler;
use App\Handler\LoginPageHandlerFactory;
use App\Handler\LoginProcessHandler;
use App\Handler\LoginProcessHandlerFactory;
use App\Handler\LogoutHandler;
use App\Handler\RegisterPageHandler;
use App\Handler\RegisterPageHandlerFactory;
use App\Handler\RegisterProcessHandler;
use App\Handler\RegisterProcessHandlerFactory;
use App\Handler\VerifyCheckHandler;
use App\Handler\VerifyCheckHandlerFactory;
use App\Handler\VerifyPageHandler;
use App\Handler\VerifyPageHandlerFactory;
use App\Handler\VerifySendHandler;
use App\Handler\VerifySendHandlerFactory;
use App\Repository\UserRepository;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Mezzio\Application;
use Mezzio\Container\ApplicationConfigInjectionDelegator;
use Twilio\Rest\Client;

class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'routes'       => $this->getRouteConfig(),
            'templates'    => $this->getTemplates(),
        ];
    }

    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
                LogoutHandler::class => LogoutHandler::class,
                UserRepository::class => UserRepository::class,
            ],
            'factories'  => [
                Client::class => TwilioClientFactory::class,
                LoginPageHandler::class => LoginPageHandlerFactory::class,
                LoginProcessHandler::class => LoginProcessHandlerFactory::class,
                RegisterPageHandler::class => RegisterPageHandlerFactory::class,
                RegisterProcessHandler::class => RegisterProcessHandlerFactory::class,
                VerifyPageHandler::class => VerifyPageHandlerFactory::class,
                VerifySendHandler::class => VerifySendHandlerFactory::class,
                VerifyCheckHandler::class => VerifyCheckHandlerFactory::class,
                DashboardHandler::class => DashboardHandlerFactory::class,
            ],
            'delegators' => [
                Application::class => [
                    ApplicationConfigInjectionDelegator::class,
                ],
            ],
        ];
    }

    public function getTemplates(): array
    {
        return [
            'paths' => [
                'app'    => [__DIR__ . '/../templates/app'],
                'error'  => [__DIR__ . '/../templates/error'],
                'layout' => [__DIR__ . '/../templates/layout'],
            ],
        ];
    }

    public function getRouteConfig(): array
    {
        return [
            [
                'path'            => '/',
                'middleware'      => LoginPageHandler::class,
                'allowed_methods' => ['GET'],
                'name'            => 'login',
            ],
            [
                'path'            => '/login',
                'middleware'      => LoginProcessHandler::class,
                'allowed_methods' => ['POST'],
                'name'            => 'login.process',
            ],
            [
                'path'            => '/register',
                'middleware'      => RegisterPageHandler::class,
                'allowed_methods' => ['GET'],
                'name'            => 'register',
            ],
            [
                'path'            => '/register',
                'middleware'      => RegisterProcessHandler::class,
                'allowed_methods' => ['POST'],
                'name'            => 'register.process',
            ],
            [
                'path'            => '/verify',
                'middleware'      => VerifyPageHandler::class,
                'allowed_methods' => ['GET'],
                'name'            => 'verify',
            ],
            [
                'path'            => '/verify/send',
                'middleware'      => VerifySendHandler::class,
                'allowed_methods' => ['POST'],
                'name'            => 'verify.send',
            ],
            [
                'path'            => '/verify',
                'middleware'      => VerifyCheckHandler::class,
                'allowed_methods' => ['POST'],
                'name'            => 'verify.check',
            ],
            [
                'path'            => '/dashboard',
                'middleware'      => DashboardHandler::class,
                'allowed_methods' => ['GET'],
                'name'            => 'dashboard',
            ],
            [
                'path'            => '/logout',
                'middleware'      => LogoutHandler::class,
                'allowed_methods' => ['GET'],
                'name'            => 'logout',
            ],
        ];
    }
}
