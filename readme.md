# Laravel 4 Smart Errors [![Build Status](https://travis-ci.org/anlutro/laravel-4-smart-errors.png?branch=master)](https://travis-ci.org/anlutro/laravel-4-smart-errors)  [![Latest Version](http://img.shields.io/github/tag/anlutro/laravel-4-smart-errors.svg)](https://github.com/anlutro/laravel-4-smart-errors/releases)

Small system for showing a very generic error message to your end-users while sending an email to yourself with all relevant information about the exception.

![Example email](http://i.imgur.com/yIvK8EV.png)

- Uncaught exceptions send an email with detailed information (referrer, route name/action, any input given and more)
- 404 errors are written in the log as warnings with the URL accessed + referrer
- Alert-level log events are sent via email (since 1.3)

Add the following to your composer.json before running `composer update`:

	require: {
		"anlutro/l4-smart-errors": "2.*"
	}

Version 2.0 and up require Laravel 4.1, 1.x works with Laravel 4.0.

Alternatively, run `composer require anlutro/l4-smart-errors`, which will automatically update your composer.json and download the package.

The package tries to maintain 5.3 compatibility but due to incompatibilities with require-dev packages, is not tested on travis. If you find a 5.3 problem with the library, please open an issue.

### IMPORTANT: Upgrading from 2.1

Behaviour has changed in 2.2 in a slightly backwards incompatible way. If 'error-view' or 'missing-view' in the config file is set to null, a view response is not returned at all from the error/404 handler. This is in order to let users add their own App::error / App::missing handlers that returns a view with custom data, instead of being forced into using a view composer if you want to use a custom view which requires specific variables to be defined.

To keep behaviour as is in 2.1, make sure the config file is published, then change your config.php file to include the following lines:

	'error-view' => 'smarterror::generic',
	'missing-view' => 'smarterror::missing',

## Usage

When the package has been downloaded, add the following to the list of service providers in app/config/app.php:

	'anlutro\L4SmartErrors\L4SmartErrorsServiceProvider',

Run `php artisan config:publish anlutro/l4-smart-errors` and open the config file that has been generated. Modify it to your needs. Copy the lang and/or views directories from the vendor directory if you want some templates to work with.

Remove any App::error and App::missing you may have in your application to prevent conflicts. If you want to handle specific types of Exceptions yourself, you can add `App::error` closures with those specific exceptions as arguments. Exceptions handled using `App::error` will not be e-mailed or logged by this package.

Exceptions are e-mailed as long as the `dev-email` key is filled out in the package config file. Make sure that your mail.php config file is correct - test it with a regular `Mail::send()`. If your mailer is incorrectly configured, you may get a blank "error in exception handler" screen upon errors.

If you want the package to send the email and log the exception data, but you want to return a custom view as a response to the end user, you can set `'error-view' => null` in the package config.php, and add the following to your app/start/global.php:

```php
App::pushError(function() {
    if (App::runningInConsole() || Config::get('app.debug')) return;
    return Response::view('my-error-view', [...], 500);
});
```

Using `pushError` instead of `error` makes sure that it's pushed to the end of the exception handler stack, giving the package's error handler priority over yours.

## Adding/customizing localization strings

This repositories' languages and translations are sporadically updated at best. To ensure that your translation is always up-to-date and/or if you want to manage your translation yourself, run the following commands.

```
mkdir -p ./app/lang/packages/MYLOCALE/smarterror`
cp ./vendor/anlutro/laravel-4-smart-errors/src/lang/en/*.php ./app/lang/packages/MYLOCALE/smarterror
```

You can also copy from a different locale than "en".

# Contribution

I'll accept language files right away without discussion. For anything else, please be descriptive in your pull requests.

# Contact

Open an issue on GitHub if you have any problems or suggestions.

# License

The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
