<?php

declare(strict_types=1);

namespace App;

use App\Factory\TwilioClientFactory;
use App\Handler\CodeRequestFormPageHandler;
use App\Handler\CodeRequestProcessingHandler;
use App\Handler\CodeRequestProcessingHandlerFactory;
use App\Handler\CodeVerificationFormPageHandler;
use App\Handler\CodeVerificationProcessingHandler;
use App\Handler\CodeVerificationProcessingHandlerFactory;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
use Mezzio\Application;
use Mezzio\Container\ApplicationConfigInjectionDelegator;
use Twilio\Rest\Client;

/**
 * The configuration provider for the App module
 *
 * @see https://docs.laminas.dev/laminas-component-installer/
 */
class ConfigProvider
{
    /**
     * Returns the configuration array
     *
     * To add a bit of a structure, each section is defined in a separate
     * method which returns an array with its configuration.
     */
    public function __invoke(): array
    {
        return [
            'dependencies' => $this->getDependencies(),
            'routes'       => $this->getRouteConfig(),
            'templates'    => $this->getTemplates(),
        ];
    }

    /**
     * Returns the container dependencies
     */
    public function getDependencies(): array
    {
        return [
            'invokables' => [
                Handler\PingHandler::class => Handler\PingHandler::class,
            ],
            'factories'  => [
                Client::class                            => TwilioClientFactory::class,
                CodeRequestFormPageHandler::class        => ReflectionBasedAbstractFactory::class,
                CodeRequestProcessingHandler::class      => CodeRequestProcessingHandlerFactory::class,
                CodeVerificationFormPageHandler::class   => ReflectionBasedAbstractFactory::class,
                CodeVerificationProcessingHandler::class => CodeVerificationProcessingHandlerFactory::class,
            ],
            'delegators' => [
                Application::class => [
                    ApplicationConfigInjectionDelegator::class,
                ],
            ],
        ];
    }

    /**
     * Returns the templates configuration
     */
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
                'middleware'      => CodeRequestFormPageHandler::class,
                'allowed_methods' => ['GET'],
                'name'            => 'code.request.form',
            ],
            [
                'path'            => '/',
                'middleware'      => CodeRequestProcessingHandler::class,
                'allowed_methods' => ['POST'],
                'name'            => 'code.request.processor',
            ],
            [
                'path'            => '/verify',
                'middleware'      => CodeVerificationFormPageHandler::class,
                'allowed_methods' => ['GET'],
                'name'            => 'code.verify.form',
            ],
            [
                'path'            => '/verify',
                'middleware'      => CodeVerificationProcessingHandler::class,
                'allowed_methods' => ['POST'],
                'name'            => 'code.verify.processor',
            ],
        ];
    }
}