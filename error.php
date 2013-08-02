<?php
// generic error handler
App::error(function(Exception $exception, $code)
{
	// get the request URL
	$url = Request::fullUrl();

	// get the current route info
	if (Route::currentRouteAction()) {
		$route = Route::currentRouteAction();
	} elseif (Route::currentRouteName()) {
		$route = Route::currentRouteName();
	} else {
		$route = 'NA (probably a closure)';
	}

	// log the exception
	Log::error("Uncaught Exception -- URL: $url -- Route: $route");
	Log::error($exception);

	// log any input
	$input = Input::all();
	if (!empty($input)) {
		Log::error('Input: ' . json_encode($input));
	}

	// if debug is false and mail.developer is set, send the mail
	if (Config::get('debug') == false && Config::has('mail.developer')) {
		Config::set('mail.pretend', false);

		$mailData = array(
			'exception' => nl2br($exception),
			'url'       => Request::fullUrl(),
			'route'     => $route,
			'input'     => $input,
		);

		Mail::send('emails.dev.error', $mailData, function($msg) use($code) {
			$msg->to(Config::get('mail.developer'))
				->subject('Error report');
		});

		return View::make('errors.generic');
	}

	// if debug is true, the default exception whoops page is shown
});

// 404 handler
App::missing(function($exception)
{
	$url = Request::fullUrl();
	$referer = Request::header('referer');

	Log::warning("404 for URL $url -- Referer: $referer");
	return Response::view('errors.missing', array(), 404);
});
