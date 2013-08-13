# Laravel 4 Smart Errors
Small system for showing a very generic error message to your end-users while sending an email to yourself with all relevant information about the exception.

- Uncaught exceptions send an email with detailed information (referrer, route name/action, any input given and more)
- 404 errors are written in the log as warnings with the URL accessed + referrer

Add the following to your composer.json before running `composer update`:

	require: {
		"anlutro/l4-smart-errors": "dev-master"
	}

Alternatively, run `composer require anlutro/l4-smart-errors`, which will automatically update your composer.json and download the package.

When the package has been downloaded, add the following to the list of service providers in app/config/app.php:

	'anlutro\L4SmartErrors\L4SmartErrorsServiceProvider',

Run `php artisan config:publish anlutro/l4-smart-errors` and open the config file that has been generated. Modify it to your needs. Copy the lang and/or views directories from the vendor directory if you want some templates to work with.

Remove any App::error and App::missing you may have in your application to prevent conflicts.

# Contribution

I'll accept language files right away without discussion. For anything else, please be descriptive in your pull requests.

# License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).