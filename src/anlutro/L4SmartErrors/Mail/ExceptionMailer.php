<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Mail;

use Exception;
use Illuminate\Foundation\Application;
use anlutro\L4SmartErrors\AppInfoGenerator;
use anlutro\L4SmartErrors\Presenters\ExceptionPresenter;
use anlutro\L4SmartErrors\Presenters\InputPresenter;
use anlutro\L4SmartErrors\Presenters\QueryLogPresenter;
use Illuminate\Mail\Message;

class ExceptionMailer
{
	public function __construct(
		Application $app,
		ExceptionPresenter $exception,
		AppInfoGenerator $appInfo,
		InputPresenter $input = null,
		QueryLogPresenter $queryLog = null
	) {
		$this->app = $app;
		$this->exception = $exception;
		$this->appInfo = $appInfo;
		$this->input = $input;
		$this->queryLog = $queryLog;
	}

	public function send(Exception $exception, $email)
	{
		if ($this->app['config']->get('smarterror::force-email') !== false) {
			$this->app['config']->set('mail.pretend', false);
		}

		if ($this->app['config']->get('smarterror::expand-stack-trace')) {
			$this->exception->setDescriptive(true);
		}

		$mailData = array(
			'info'      => $this->appInfo,
			'exception' => $this->exception,
			'input'     => $this->input,
			'queryLog'  => $this->queryLog,
		);

		$env = $this->app->environment();

		$exceptionName = $this->getExceptionBaseName($exception);
		$subject = "[$env] $exceptionName - ";
		$subject .= $this->app['request']->root() ?: $this->app['config']->get('app.url');
		$htmlView = $this->app['config']->get('smarterror::error-email-view') ?: 'smarterror::error-email';
		$plainView = $this->app['config']->get('smarterror::error-email-view-plain') ?: 'smarterror::error-email-plain';

		$callback = function(Message $msg) use($email, $subject) {
			$msg->to($email)->subject($subject);
		};

		$this->app['mailer']->send(array($htmlView, $plainView), $mailData, $callback);
	}

	protected function getExceptionBaseName(Exception $exception)
	{
		$exceptionName = get_class($exception);

		if (($pos = strrpos($exceptionName, '\\')) !== false) {
			$exceptionName = substr($exceptionName, ($pos + 1));
		}

		return $exceptionName;
	}
}
