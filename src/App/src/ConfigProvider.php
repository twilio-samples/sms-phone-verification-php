<?php

declare(strict_types=1);

namespace App;

use App\Factory\TwilioClientFactory;
use App\Handler\CodeRequestFormPageHandler;
use App\Handler\CodeRequestProcessingHandler;
use App\Handler\CodeVerificationFormPageHandler;
use Laminas\ServiceManager\AbstractFactory\ReflectionBasedAbstractFactory;
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
                Client::class                                    => TwilioClientFactory::class,
                CodeRequestFormPageHandler::class                => ReflectionBasedAbstractFactory::class,
                CodeRequestProcessingHandler::class              => ReflectionBasedAbstractFactory::class,
                CodeVerificationFormPageHandler::class           => ReflectionBasedAbstractFactory::class,
                Handler\CodeVerificationProcessingHandler::class => ReflectionBasedAbstractFactory::class,
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
}
