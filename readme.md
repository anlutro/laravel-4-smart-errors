# Laravel 4 Smart Errors

[![Build Status](https://travis-ci.org/anlutro/laravel-4-smart-errors.png?branch=master)](https://travis-ci.org/anlutro/laravel-4-smart-errors)
[![Latest Stable Version](https://poser.pugx.org/anlutro/l4-smart-errors/v/stable.svg)](https://github.com/anlutro/laravel-4-smart-errors/releases)
[![Latest Unstable Version](https://poser.pugx.org/anlutro/l4-smart-errors/v/unstable.svg)](https://github.com/anlutro/laravel-4-smart-errors/branches/active)
[![License](https://poser.pugx.org/anlutro/l4-smart-errors/license.svg)](http://opensource.org/licenses/MIT)

Small system for showing a very generic error message to your end-users while sending an email to yourself with all relevant information about the exception.

![Example email](http://i.imgur.com/yIvK8EV.png)

- Uncaught exceptions send an email with detailed information (referrer, route name/action, any input given and more)
- 404 errors are written in the log as warnings with the URL accessed + referrer
- Alert-level log events are sent via email (since 1.3)

NOTE: Laravel 5 is not supported. See [this issue](https://github.com/anlutro/laravel-4-smart-errors/issues/24).


## Installation

To install, run `composer require anlutro/l4-smart-errors`. This will pick the most appropriate version and add it to your `composer.json`.

NOTE: The package tries to maintain PHP 5.3 compatibility but due to incompatibilities with require-dev packages, PHP 5.3 not tested on travis. If you find a PHP 5.3-related problem with the library, please open an issue.


### IMPORTANT: Upgrading from 2.1

Behaviour has changed in 2.2 in a slightly backwards incompatible way. If 'error-view' or 'missing-view' in the config file is set to null, a view response is not returned at all from the error/404 handler. This is in order to let users add their own App::error / App::missing handlers that returns a view with custom data, instead of being forced into using a view composer if you want to use a custom view which requires specific variables to be defined.

To keep behaviour as is in 2.1, make sure the config file is published, then change your config.php file to include the following lines:

	'error-view' => 'smarterror::generic',
	'missing-view' => 'smarterror::missing',


## Usage

When the package has been downloaded, add the following to the list of service providers in `app/config/app.php`:

	'anlutro\L4SmartErrors\L4SmartErrorsServiceProvider',

Run `php artisan config:publish anlutro/l4-smart-errors` and open the config file that has been generated. Modify it to your needs. Copy the lang and/or views directories from the vendor directory if you want some templates to work with.

Remove any `App::error` and `App::missing` you may have in your application to prevent conflicts. If you want to handle specific types of exceptions yourself, you can add `App::error` closures with those specific exceptions as arguments. Exceptions handled using `App::error` will not be e-mailed or logged by this package.


### Exception email reports

Exceptions are e-mailed as long as `app.debug` is true, and the `dev-email` key is filled out in the package config file. Make sure that your `mail.php` config file is correct - test it with a regular `Mail::send()`. If your mailer is incorrectly configured, you may get a blank "error in exception handler" screen upon errors.

Email reports are throttled, so that the exact same exception won't be sent over and over again. By default, the threshold for when an identical exception should be emailed again is 10 minutes. This can be configured with the `throttle-age` config key.

Note that emails are not sent when `app.debug` is false.


### End-user responses

For any uncaught/unhandled exceptions, the package will return a generic error response to your end users unless `app.debug` is true. If you get this generic response while developing, you might not be setting the correct environment - check your `bootstrap/start.php`.

You can configure which view is displayed with the `error-view`, `missing-view` and `csrf-view` config values. If you set these to `null`, the package will **not** return a generic response to your end users, allowing you to implement your own, as shown in this example:

```php
// app/start/global.php
App::pushError(function($exception) {
    if (App::runningInConsole() || Config::get('app.debug')) return;
    return Response::view('my-error-view', [...], 500);
});
```

Using `pushError` instead of `error` makes sure that it's pushed to the end of the exception handler stack, giving the package's error handler priority over yours.


### Localizing the response

This repositories' languages and translations are sporadically updated at best. To ensure that your translation is always up-to-date and/or if you want to manage your translation yourself, run the following commands.

```
mkdir -p ./app/lang/packages/MYLOCALE/smarterror`
cp ./vendor/anlutro/laravel-4-smart-errors/src/lang/en/*.php ./app/lang/packages/MYLOCALE/smarterror
```

You can also copy from a different locale than "en".

If your locale is missing, your generic end-user responses will only have placeholder strings. You can make this default to english by putting `'fallback_locale' => 'en',` into `app/config/app.php`.


## Contribution

I'll accept language files right away without discussion. For anything else, please be descriptive in your pull requests.


## Contact

Open an issue on GitHub if you have any problems or suggestions.


## License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
