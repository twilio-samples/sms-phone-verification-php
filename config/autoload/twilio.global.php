<?php

declare(strict_types=1);

// phpcs:disable PSR12.Files.FileHeader.IncorrectOrder

/**
 * Through the magic of PHPDotenv, this file will make your Twilio credentials available in the
 * application's configuration.
 */

return [
    'twilio' => [
        'account_sid'      => $_ENV['TWILIO_ACCOUNT_SID'],
        'auth_token'       => $_ENV['TWILIO_AUTH_TOKEN'],
        'verification_sid' => $_ENV['TWILIO_VERIFICATION_SID'],
    ],
];
