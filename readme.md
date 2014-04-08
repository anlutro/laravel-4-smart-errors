# Laravel 4 Smart Errors
[![Build Status](https://travis-ci.org/anlutro/laravel-4-smart-errors.png?branch=master)](https://travis-ci.org/anlutro/laravel-4-smart-errors)  [![Latest Version](http://img.shields.io/github/tag/anlutro/laravel-4-smart-errors.svg)](https://github.com/anlutro/laravel-4-smart-errors/releases)

Small system for showing a very generic error message to your end-users while sending an email to yourself with all relevant information about the exception.

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

## Usage
When the package has been downloaded, add the following to the list of service providers in app/config/app.php:

	'anlutro\L4SmartErrors\L4SmartErrorsServiceProvider',

Run `php artisan config:publish anlutro/l4-smart-errors` and open the config file that has been generated. Modify it to your needs. Copy the lang and/or views directories from the vendor directory if you want some templates to work with.

Remove any App::error and App::missing you may have in your application to prevent conflicts. If you want to handle specific types of Exceptions yourself, you can add App::error closures with those specific exceptions as arguments.

If you want to mail yourself on an error but not dump the user to a generic error screen, you can do so via the facade:

	$result = \anlutro\L4SmartErrors\SmartError::handleException($exception);

`handleException` will return the generic error view if you'd like to use it for something.

# Contribution
I'll accept language files right away without discussion. For anything else, please be descriptive in your pull requests.

# Contact
Open an issue on GitHub if you have any problems or suggestions.

# License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).
