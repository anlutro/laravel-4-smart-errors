<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   Laravel 4 Smart Errors
 */

namespace anlutro\L4SmartErrors;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\Response;
use Exception;

class L4SmartErrorsServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * The name of the package.
	 *
	 * @var string
	 */
	protected $package = 'anlutro/l4-smart-errors';

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$pkg = $this->package;

		$this->app['smarterror'] = $this->app->share(function($app) use ($pkg) {
			$handler = new ErrorHandler($pkg);
			$handler->setApplication($app);
			return $handler;
		});
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package($this->package, $this->package);

		// $this->app in closures won't work in php 5.3
		$app = $this->app;

		// register the error handler
		$this->app->error(function(Exception $exception, $code) use ($app) {
			return $app['smarterror']->handleException($exception, $code);
		});

		// register the 404 handler
		$this->app->missing(function($exception) use ($app) {
			return new Response($app['smarterror']->handleMissing($exception), 404);
		});

		// allow our event handler to be triggered via events
		$this->app['events']->listen('smarterror', function($exception) use ($app) {
			$app['smarterror']->handleException($exception, null, true);
		});

		// register the alert level log listener
		$this->app['log']->listen(function($level, $message, $context) use ($app) {
			if ($level == 'alert') {
				$app['smarterror']->handleAlert($message, $context);
			}
		});
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array('smarterror');
	}

}
