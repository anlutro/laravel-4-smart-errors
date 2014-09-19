<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

use Exception;
use Illuminate\Foundation\Application;
use Illuminate\Session\TokenMismatchException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

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
	 * @var \SplObjectStorage
	 */
	protected $handledExceptions;

	/**
	 * @param \Illuminate\Foundation\Application $app
	 */
	public function __construct(Application $app)
	{
		$this->app = $app;
		$this->handledExceptions = new \SplObjectStorage;
	}

	/**
	 * Get the application logger.
	 *
	 * @return \Psr\Log\LoggerInterface
	 */
	protected function getLogger()
	{
		$logger = $this->app['log'];

		if ($logger instanceof \Illuminate\Log\Writer) {
			$logger = $logger->getMonolog();
		}

		if ($logger instanceof \Psr\Log\LoggerInterface) {
			return $logger;
		}

		return null;
	}

	/**
	 * Handle an uncaught exception. Returns a view if config.app.debug == false,
	 * otherwise returns void to let the default L4 error handler do its job.
	 *
	 * @param  \Exception $exception
	 * @param  integer   $code
	 *
	 * @return \Illuminate\Http\Response|null
	 */
	public function handleException(Exception $exception, $code = null)
	{
		try {
			if ($this->exceptionHasBeenHandled($exception)) return null;

			list($exceptionPresenter, $appInfoPresenter, $inputPresenter, $queryLogPresenter)
				= $this->makeAllPresenters($exception);

			if ($logger = $this->getLogger()) {
				$this->app->make('anlutro\L4SmartErrors\Log\ExceptionLogger',
					[$logger, $appInfoPresenter, $inputPresenter])
					->log($exception);
			}

			$email = $this->app['config']->get('smarterror::dev-email');

			if ($email && $this->shouldSendEmail($exception)) {
				$this->app->make('anlutro\L4SmartErrors\Mail\ExceptionMailer',
					[$this->app, $exceptionPresenter, $appInfoPresenter, $inputPresenter, $queryLogPresenter])
					->send($email);
			}

			return $this->app->make('anlutro\L4SmartErrors\Responders\ExceptionResponder', [$this->app])
				->respond($exception);
		} catch (Exception $e) {
			return $this->handleHandlerException($e);
		}
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

		$this->app->make('anlutro\L4SmartErrors\Mail\AlertLogMailer',
			[$this->app, $message, $this->makeLogContextPresenter($context), $this->makeAppInfoGenerator()])
			->send($email);
	}

	/**
	 * Handle a 404 error.
	 *
	 * @param  \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception
	 *
	 * @return \Illuminate\Http\Response|null
	 */
	public function handleMissing(NotFoundHttpException $exception)
	{
		try {
			if ($this->exceptionHasBeenHandled($exception)) return null;

			if ($logger = $this->getLogger()) {
				$this->app->make('anlutro\L4SmartErrors\Log\MissingLogger',
					[$logger, $this->app['request']])
				->log();
			}

			return $this->app->make('anlutro\L4SmartErrors\Responders\MissingResponder', [$this->app])
				->respond($exception);
		} catch (Exception $e) {
			return $this->handleHandlerException($e);
		}
	}

	/**
	 * Handle a CSRF token mismatch exception.
	 *
	 * @param  \Illuminate\Session\TokenMismatchException $exception
	 *
	 * @return \Illuminate\Http\Response|null
	 */
	public function handleTokenMismatch(TokenMismatchException $exception)
	{
		try {
			if ($this->exceptionHasBeenHandled($exception)) return null;

			if ($logger = $this->getLogger()) {
				$this->app->make('anlutro\L4SmartErrors\Log\CsrfLogger',
					[$logger, $this->makeAppInfoGenerator()])
					->log();
			}

			return $this->app->make('anlutro\L4SmartErrors\Responders\CsrfResponder', [$this->app])
				->respond($exception);
		} catch (Exception $e) {
			return $this->handleHandlerException($e);
		}
	}

	/**
	 * Determine if an exception has been previously handled or not.
	 *
	 * @param  \Exception $exception
	 *
	 * @return boolean
	 */
	protected function exceptionHasBeenHandled(Exception $exception)
	{
		if ($this->handledExceptions->contains($exception)) return true;
		$this->handledExceptions->attach($exception);
		return false;
	}

	/**
	 * Handle an exception that occurs inside an error handler.
	 *
	 * @param  Exception $exception
	 *
	 * @return string
	 */
	protected function handleHandlerException(Exception $exception)
	{
		error_log('Error in exception handler - https://github.com/anlutro/laravel-4-smart-errors/issues');

		$lines = preg_split('{[\r\n]+}', (string) $exception);
		foreach ($lines as $line) {
			error_log($line);
		}

		return 'Error in exception handler. Check system error logs.';
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

		// if the mailer is not bound to the IoC container...
		if (!$this->app->bound('mailer')) return false;

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
	 * Get an array of all the different presenters available.
	 *
	 * @param  \Exception $exception
	 *
	 * @return array
	 */
	protected function makeAllPresenters(Exception $exception)
	{
		return array(
			$this->makeExceptionPresenter($exception),
			$this->makeAppInfoGenerator(),
			$this->makeInputPresenter(),
			$this->makeQueryLogPresenter(),
		);
	}

	/**
	 * Make an exception presenter.
	 *
	 * @param  \Exception $exception
	 *
	 * @return \anlutro\L4SmartErrors\Presenters\ExceptionPresenter
	 */
	protected function makeExceptionPresenter($exception)
	{
		return $this->app->make('anlutro\L4SmartErrors\Presenters\ExceptionPresenter',
			[$exception]);
	}

	/**
	 * Make an application information generator.
	 *
	 * @return \anlutro\L4SmartErrors\AppInfoGenerator
	 */
	protected function makeAppInfoGenerator()
	{
		return $this->app->make('anlutro\L4SmartErrors\AppInfoGenerator',
			[$this->app]);
	}

	/**
	 * Make an input presenter. Returns null if no input is available.
	 *
	 * @return \anlutro\L4SmartErrors\Presenters\InputPresenter|null
	 */
	protected function makeInputPresenter()
	{
		$input = $this->app['request']->all();

		if (count($input) < 1) {
			return null;
		}

		return $this->app->make('anlutro\L4SmartErrors\Presenters\InputPresenter',
			[$input]);
	}

	/**
	 * Make a query log presenter. Returns null if mailing of query log is
	 * disabled in the smart-error config.
	 *
	 * @return \anlutro\L4SmartErrors\Presenters\QueryLogPresenter|null
	 */
	protected function makeQueryLogPresenter()
	{
		if (!$this->app['config']->get('smarterror::include-query-log')) {
			return null;
		}

		return $this->app->make('anlutro\L4SmartErrors\Presenters\QueryLogPresenter',
			[$this->app['db']->getQueryLog()]);
	}

	/**
	 * Make a log context presenter.
	 *
	 * @return \anlutro\L4SmartErrors\Presenters\LogContextPresenter
	 */
	protected function makeLogContextPresenter(array $context)
	{
		return $this->app->make('anlutro\L4SmartErrors\Presenters\LogContextPresenter',
			[$context]);
	}
}
