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

class L5SmartErrorsServiceProvider extends L4SmartErrorsServiceProvider
{
	public function register()
	{
		parent::register();

		$this->mergeConfigFrom(
			dirname(dirname(__DIR__)).'/config/config.php', 'smarterror'
		);
	}

	public function boot()
	{
		$respath = dirname(dirname(__DIR__));
		$this->loadViewsFrom($respath.'/views', 'smarterror');
		$this->loadTranslationsFrom($respath.'/lang', 'smarterror');

		$paths = [
			dirname(dirname(__DIR__)).'/config/config.php' => config_path('smarterror.php'),
		];
		$this->publishes($paths, 'smarterror');

 		$this->registerAlertLogListener();
	}
}
