<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

use Illuminate\Support\Facades\Response;
use Illuminate\Foundation\Application;
use Symfony\Component\Debug\Exception\FatalErrorException;

/**
 * The class that handles the errors. Obviously
 */
class ErrorHandler
{
	/**
	 * @var \Illuminate\Foundation\Application
	 */
	protected $app;

	/**
	 * @var array
	 */
	protected $handledExceptions = array();

	/**
	 * @param \Illuminate\Foundation\Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
	}

	/**
	 * Handle an uncaught exception. Returns a view if config.app.debug == false,
	 * otherwise returns void to let the default L4 error handler do its job.
	 *
	 * @param  \Exception $exception
	 * @param  integer   $code
	 *
	 * @return \Illuminate\Http\Response|void
	 */
	public function handleException($exception, $code = null)
	{
		if (in_array($exception, $this->handledExceptions)) return;
		$this->handledExceptions[] = $exception;

		list($exceptionPresenter, $appInfoPresenter, $inputPresenter, $queryLogPresenter) = $this->makeAllPresenters($exception);

		with(new Log\ExceptionLogger($this->app['log'], $appInfoPresenter, $inputPresenter))
			->log($exception);

		$email = $this->app['config']->get('smarterror::dev-email');

		if ($email && $this->shouldSendEmail($exception)) {
			with(new Mail\ExceptionMailer($this->app, $exceptionPresenter, $appInfoPresenter, $inputPresenter, $queryLogPresenter))
				->send($exception, $email);
		}

		return with(new Responders\ExceptionResponder($this->app))
			->respond($exception);
	}

	/**
	 * Determine if the error handler should send an email.
	 *
	 * @param  \Exception $exception
	 *
	 * @return boolean
	 */
	protected function shouldSendEmail($exception)
	{
		// if app.debug is true, no emails should be sent
		if ($this->app['config']->get('app.debug') === true) return false;

		$files = $this->app['files'];
		$path = $this->app['config']->get('smarterror::storage-path');

		// create a basic hash of the exception. this should include the stack
		// trace and message, making it more or less a unique identifier
		$string = $exception->getMessage().$exception->getCode()
			.$exception->getTraceAsString();
		$hash = base64_encode($string);

		$data = array();

		// if the file exists, read from it and check if the hash of the current
		// exception is the same as the previous one.
		if ($files->exists($path)) {
			$data = json_decode($files->get($path), true);
			if (isset($data['previous']) && $data['previous'] == $hash) {
				return false;
			}
		}

		// if the file is writeable, write the current exception hash into it.
		if ($this->pathIsWriteable($path)) {
			$data['previous'] = $hash;
			$files->put($path, json_encode($data));
		}

		return true;
	}

	/**
	 * Determine if a path is writeable or not.
	 *
	 * @param  string $path
	 *
	 * @return boolean
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function pathIsWriteable($path)
	{
		$files = $this->app['files'];

		if ($files->isDirectory($path)) {
			throw new \InvalidArgumentException("$path is a directory");
		}

		// if the file exists, simply check if it is writeable
		if ($files->isFile($path) && $files->isWritable($path)) {
			return true;
		}

		// if not, check if the directory of the path is writeable
		$dir = dirname($path);

		if ($files->isDirectory($dir) && $files->isWritable($dir)) {
			return true;
		}

		return false;
	}

	/**
	 * Handle an alert-level logging event.
	 *
	 * @param  string $message
	 * @param  array  $context
	 *
	 * @return void
	 */
	public function handleAlert($message, $context)
	{
		$email = $this->app['config']->get('smarterror::dev-email');

		if ($this->app['config']->get('app.debug') !== false || empty($email)) {
			return;
		}

		if ($this->app['config']->get('smarterror::force-email') !== false) {
			$this->app['config']->set('mail.pretend', false);
		}

		with(new Mail\AlertLogMailer($this->app, $message, $this->makeLogContextPresenter($context), $this->makeAppInfoGenerator()))
			->send($email);
	}

	/**
	 * Handle a 404 error.
	 *
	 * @param  \Exception $exception
	 *
	 * @return \Illuminate\Http\Response|void
	 */
	public function handleMissing($exception)
	{
		if (in_array($exception, $this->handledExceptions)) return;
		$this->handledExceptions[] = $exception;

		with(new Log\MissingLogger($this->app['log'], $this->app['request']))
			->log();

		return with(new Responders\MissingResponder($this->app))
			->respond($exception);
	}

	/**
	 * Handle a CSRF token mismatch exception.
	 *
	 * @param  \Illuminate\Session\TokenMismatchException $exception
	 *
	 * @return \Illuminate\Http\Response|void
	 */
	public function handleTokenMismatch($exception)
	{
		if (in_array($exception, $this->handledExceptions)) return;
		$this->handledExceptions[] = $exception;

		with(new Log\CsrfLogger($this->app['log'], $this->makeAppInfoGenerator()))
			->log();

		return with(new Responders\CsrfResponder($this->app))
			->respond($exception);
	}

	protected function makeAllPresenters($exception)
	{
		return array(
			$this->makeExceptionPresenter($exception),
			$this->makeAppInfoGenerator(),
			$this->makeInputPresenter(),
			$this->makeQueryLogPresenter(),
		);
	}

	protected function makeExceptionPresenter($exception)
	{
		return new Presenters\ExceptionPresenter($exception);
	}

	protected function makeAppInfoGenerator()
	{
		return new AppInfoGenerator($this->app);
	}

	protected function makeInputPresenter()
	{
		$input = $this->app['request']->all();
		return empty($input) ? null : new Presenters\InputPresenter($input);
	}

	protected function makeQueryLogPresenter()
	{
		if ($this->app['config']->get('smarterror::include-query-log')) {
			return new Presenters\QueryLogPresenter($this->app['db']->getQueryLog());
		}

		return null;
	}

	protected function makeLogContextPresenter(array $context)
	{
		return new Presenters\LogContextPresenter($context);
	}

	/**
	 * Get the action or name of the current route.
	 *
	 * @return array
	 */
	protected function findRouteNames()
	{
		$route = $this->app['router']->current();

		if (!$route) {
			return array(null, null);
		} else {
			return array($route->getActionName(), $route->getName());
		}
	}

	/**
	 * Determine whether a JSON response should be returned.
	 *
	 * @return bool
	 */
	protected function requestIsJson()
	{
		$request = $this->app['request'];
		return $request->wantsJson() || $request->isJson() || $request->ajax();
	}
}
