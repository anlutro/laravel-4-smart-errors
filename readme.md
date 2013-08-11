# Laravel 4 Smart Errors
Small system for showing a very generic error message to your end-users while sending an email to yourself with all relevant information about the exception.

- Uncaught exceptions send an email with detailed information (referrer, route name/action, any input given and more)
- 404 errors are written in the log as warnings with the URL accessed + referrer
- Simple maintenance mode handler

Copy the app folder into your project, answering yes to any questions about overwriting. Add an include statement for `error.php` in `app/start/global.php` and remove the default `App::error`, `App::missing` and `App::down` handlers.

Add a "developer" key to `app/config/mail.php` containing your or your dev team's email address. Example:

	'developer' => 'webdev@example.com'

Make sure HTML errors are turned on in your php.ini.

# License
The contents of this repository is released under the [MIT license](http://opensource.org/licenses/MIT).