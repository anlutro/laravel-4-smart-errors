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
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('anlutro/l4-smart-errors', 'anlutro/l4-smart-errors');

		// $this->app in closures won't work in php 5.3
		$app = $this->app;

		$this->app->error(function(Exception $exception, $code) use ($app) {
			$handler = new ErrorHandler($app);
			return $handler->handleException($exception, $code);
		});

		$this->app->missing(function($exception) use ($app) {
			$handler = new ErrorHandler($app);
			return $handler->handleMissing($exception);
		});
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return array();
	}

}
