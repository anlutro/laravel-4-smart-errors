<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

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
		if ($this->app->bound('Psr\Log\LoggerInterface')) {
			return $this->app->make('Psr\Log\LoggerInterface');
		}

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
	public function handleException($exception, $code = null)
	{
		try {
			if ($this->exceptionHasBeenHandled($exception)) {
				return null;
			}

			if ($logger = $this->getLogger()) {
				$this->app->make('anlutro\L4SmartErrors\Log\ExceptionLogger',
					[$logger, $this->makeContextCollector()])
					->log($exception);
			}

			$email = $this->app['config']->get('smarterror::dev-email');

			if ($email && $this->shouldSendEmail($exception)) {
				$appInfoGenerator = $this->makeAppInfoGenerator();
				$exceptionPresenter = $this->makeExceptionPresenter($exception);
				$sessionPresenter = $this->makeSessionPresenter();
				$inputPresenter = $this->makeInputPresenter();
				$queryLogPresenter = $this->makeQueryLogPresenter();

				$this->app->make('anlutro\L4SmartErrors\Mail\ExceptionMailer',
					[$this->app, $exceptionPresenter, $appInfoGenerator, $sessionPresenter, $inputPresenter, $queryLogPresenter])
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

		if ($this->app['config']->get('app.debug') !== false || !$email) {
			return;
		}

		$forcing = false;
		if ($this->app['config']->get('smarterror::force-email') !== false) {
			$forcing = true;
			$previousPretendState = $this->app['config']->get('mail.pretend');
			$this->app['config']->set('mail.pretend', false);
		}

		$this->app->make('anlutro\L4SmartErrors\Mail\AlertLogMailer',
			[$this->app, $message, $this->makeLogContextPresenter($context), $this->makeAppInfoGenerator()])
			->send($email);

		if ($forcing) {
			$this->app['config']->set('mail.pretend', $previousPretendState);
		}
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
			if ($this->exceptionHasBeenHandled($exception)) {
				return null;
			}

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
			if ($this->exceptionHasBeenHandled($exception)) {
				return null;
			}

			if ($logger = $this->getLogger()) {
				$sessionToken = $this->app['session']->getToken();
				$inputToken = $this->app['request']->get('_token');
				$this->app->make('anlutro\L4SmartErrors\Log\CsrfLogger',
					[$logger, $this->makeContextCollector(), $sessionToken, $inputToken])
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
	protected function exceptionHasBeenHandled($exception)
	{
		if ($this->handledExceptions->contains($exception)) {
			return true;
		}

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
	protected function handleHandlerException($exception)
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
		// if the mailer is not bound to the IoC container...
		if (!$this->app->bound('mailer') && !$this->app->isDeferredService('mailer')) {
			return false;
		}

		$throttler = $this->app->make('anlutro\L4SmartErrors\ReportThrottler',
			[$this->app['config'], $this->app['files']]);

		return $throttler->shouldReport($exception);
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
	 * Make an application log context collector.
	 *
	 * @return \anlutro\L4SmartErrors\Log\ContextCollector
	 */
	protected function makeContextCollector()
	{
		return $this->app->make('anlutro\L4SmartErrors\Log\ContextCollector',
			[$this->app, $this->makeInputPresenter(), $this->makeSessionPresenter()]);
	}

	/**
	 * Make a session presenter.
	 *
	 * @return \anlutro\L4SmartErrors\Presenters\SessionPresenter|null
	 */
	protected function makeSessionPresenter()
	{
		$session = $this->app['session'];
		$sessionData = $session->all();

		if (count($sessionData) < 1) {
			return null;
		}

		$fields = $this->app['config']->get('smarterror::session-wipe', []);

		return $this->app->make('anlutro\L4SmartErrors\Presenters\SessionPresenter',
			[$session->getId(), $sessionData, $fields]);
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

		$fields = $this->app['config']->get('smarterror::input-wipe', ['password']);

		return $this->app->make('anlutro\L4SmartErrors\Presenters\InputPresenter',
			[$input, $fields]);
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
