# Laravel 4 Smart Errors
Small system for showing a very generic error message to your end-users while sending an email to yourself with all relevant information about the exception.

- Uncaught exceptions send an email with detailed information (referrer, route name/action, any input given and more)
- 404 errors are written in the log as warnings with the URL accessed + referrer
- Alert-level log events are sent via email (since 1.3)

Add the following to your composer.json before running `composer update`:

	require: {
		"anlutro/l4-smart-errors": "dev-master"
	}

Alternatively, run `composer require anlutro/l4-smart-errors`, which will automatically update your composer.json and download the package.

When the package has been downloaded, add the following to the list of service providers in app/config/app.php:

	'anlutro\L4SmartErrors\L4SmartErrorsServiceProvider',

Run `php artisan config:publish anlutro/l4-smart-errors` and open the config file that has been generated. Modify it to your needs. Copy the lang and/or views directories from the vendor directory if you want some templates to work with.

Remove any App::error and App::missing you may have in your application to prevent conflicts. If you want to handle specific types of Exceptions yourself, you can add App::error closures with those specific exceptions as arguments.

# Non-fatal error handling
If you want to mail yourself on an error but not dump the user to a generic error screen, you can either fire a Laravel event:

	try {
		// something
	} catch (SpecificExcpetion $e) {
		Event::fire('smarterror', array($e));
		// display nice error message
	}

Uncaught exceptions in this snippet would trigger the error mail as usual.

You can also instanciate the error handler yourself, which is slightly more difficult as you need to get the Illuminate\Foundation\Application instance somehow.

	$handler = new \anlutro\L4SmartErrors\ErrorHandler($app);
	$handler->handleException($exception);

`handleException` will return the generic error view if you'd like to use it for something.

# Contribution
I'll accept language files right away without discussion. For anything else, please be descriptive in your pull requests.

If anyone wants to make a better-looking layout - mine is pretty ugly, I admit - please open an issue so we can discuss the matter.

# Contact
Open an issue on GitHub if you have any problems or suggestions.

If you have any questions or want to have a chat, look for anlutro @ irc.freenode.net.

# License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).