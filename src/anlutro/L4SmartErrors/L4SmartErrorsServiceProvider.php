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
use Illuminate\Session\TokenMismatchException;

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

		$this->registerErrorHandler();
		$this->registerTokenMismatchHandler();
		$this->registerMissingHandler();
		$this->registerAlertLogListener();
	}

	protected function registerErrorHandler()
	{
		$app = $this->app;
		$this->app->error(function($exception, $code) use ($app) {
			return $app['smarterror']->handleException($exception, $code);
		});
	}

	protected function registerTokenMismatchHandler()
	{
		$app = $this->app;
		$this->app->error(function(TokenMismatchException $exception, $code) use ($app) {
			return $app['smarterror']->handleTokenMismatch($exception);
		});
	}

	protected function registerMissingHandler()
	{
		$app = $this->app;
		$this->app->missing(function($exception) use ($app) {
			return $app['smarterror']->handleMissing($exception);
		});
	}

	protected function registerAlertLogListener()
	{
		$app = $this->app;
		$callback = function($level, $message, $context) use ($app) {
			if ($level == 'critical' || $level == 'alert' || $level == 'emergency') {
				$app['smarterror']->handleAlert($message, $context);
			}
		};
		$this->app['events']->listen('illuminate.log', $callback);
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
