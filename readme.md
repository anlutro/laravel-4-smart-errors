# Laravel 4 Smart Errors
Small system for showing a very generic error message to your end-users while sending an email to yourself with all relevant information about the exception.

Also adds a small 404 error page and logs a little information about the 404.

Copy error.php to your app folder and make sure it's included somewhere (`app/start/global.php` is a good place). Copy the views folder as well.

Add a developer key in `app/config/mail.php` - for example:

	'developer' => 'webdev@example.com',

# License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).