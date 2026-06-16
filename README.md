# Build a User Registration System with SMS Phone Verification

A PHP Mezzio application with user registration, authentication, and SMS phone verification using Twilio Verify. Users create an account, verify their phone number via OTP, and access protected content.

![Register](assets/register.png)

![Verify](assets/verify.png)

## Set up

### Requirements

- [PHP](https://www.php.net/) 8.3+
- [Composer](https://getcomposer.org/)
- [A Twilio Verify Service](https://console.twilio.com/?frameUrl=/console/verify/services)

### Twilio Account Settings

| Config Value | Description |
| :----------- | :---------- |
| TWILIO_ACCOUNT_SID | Your Twilio Account SID from the [Console](https://www.twilio.com/console) |
| TWILIO_AUTH_TOKEN | Your Twilio Auth Token from the [Console](https://www.twilio.com/console) |
| TWILIO_VERIFICATION_SID | Create a Verify Service [here](https://www.twilio.com/console/verify/services) |

### Local development

1. Clone this repository and `cd` into it.

   ```bash
   git clone git@github.com:twilio-samples/sms-phone-verification-php.git
   cd sms-phone-verification-php
   ```

2. Install dependencies.

   ```bash
   composer install
   ```

3. Set your environment variables.

   ```bash
   cp .env.example .env
   ```

   Edit `.env` with your Twilio credentials.

4. Run the application.

   ```bash
   composer serve
   ```

5. Open http://localhost:3000 to register an account and verify your phone number.

## Resources

- [Twilio Verify API Documentation](https://www.twilio.com/docs/verify/api)
- [SMS Phone Verification CodeExchange Page](https://www.twilio.com/code-exchange/sms-phone-verification)

## License

[MIT](http://www.opensource.org/licenses/mit-license.html)
