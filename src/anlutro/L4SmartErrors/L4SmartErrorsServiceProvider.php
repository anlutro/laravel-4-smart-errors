<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

use Illuminate\Support\ServiceProvider;

class L4SmartErrorsServiceProvider extends ServiceProvider
{
	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->app->bindShared('smarterror', function($app) {
			return new ErrorHandler($app);
		});
	}

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('anlutro/l4-smart-errors', 'smarterror');

		// $this->app in closures won't work in php 5.3
		$app = $this->app;

		// register the error handler
		$this->app->error(function(\Exception $exception, $code) use ($app) {
			return $app['smarterror']->handleException($exception, $code);
		});

		// register the 404 handler
		$this->app->missing(function($exception) use ($app) {
			return $app['smarterror']->handleMissing($exception);
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
