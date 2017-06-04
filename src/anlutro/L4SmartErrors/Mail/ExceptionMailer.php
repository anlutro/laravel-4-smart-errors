<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Mail;

use Illuminate\Foundation\Application;
use anlutro\L4SmartErrors\AppInfoGenerator;
use anlutro\L4SmartErrors\Presenters\ExceptionPresenter;
use anlutro\L4SmartErrors\Presenters\SessionPresenter;
use anlutro\L4SmartErrors\Presenters\InputPresenter;
use anlutro\L4SmartErrors\Presenters\QueryLogPresenter;
use Illuminate\Mail\Message;

class ExceptionMailer
{
	protected $app;
	protected $exception;
	protected $session;
	protected $appInfo;
	protected $input;
	protected $queryLog;

	public function __construct(
		Application $app,
		ExceptionPresenter $exception,
		AppInfoGenerator $appInfo,
		SessionPresenter $session = null,
		InputPresenter $input = null,
		QueryLogPresenter $queryLog = null
	) {
		$this->app = $app;
		$this->exception = $exception;
		$this->session = $session;
		$this->appInfo = $appInfo;
		$this->input = $input;
		$this->queryLog = $queryLog;
	}

	public function send($email)
	{
		$config = $this->app['config'];

		if ($config->get('smarterror::force-email')) {
			$config->set('mail.pretend', false);
		}

		if ($config->get('smarterror::expand-stack-trace')) {
			$this->exception->setDescriptive(true);
		}

		$mailData = array(
			'info'      => $this->appInfo,
			'exception' => $this->exception,
			'session'   => $this->session,
			'input'     => $this->input,
			'queryLog'  => $this->queryLog,
		);

		$env = $this->app->environment();

		$htmlView = $config->get('smarterror::error-email-view') ?: 'smarterror::error-email';
		$plainView = $config->get('smarterror::error-email-view-plain') ?: 'smarterror::error-email-plain';

		$exceptionName = $this->getExceptionBaseName($this->exception->getException());
		$rootUrl = $this->app['request']->root() ?: $config->get('app.url');
		$config = array(
			'subject' => "[$env] $exceptionName - $rootUrl",
			'from' => $config->get('smarterror::email-from'),
			'cc' => $config->get('smarterror::cc-email'),
		);

		$callback = function(Message $msg) use($email, $config) {
			$msg->to($email);
			if (isset($config['from']) && $config['from']) {
				$msg->from($config['from']);
			}
			if (isset($config['subject']) && $config['subject']) {
				$msg->subject($config['subject']);
			}
			if (isset($config['cc']) && $config['cc']) {
				$msg->cc($config['cc']);
			}
		};

		$this->app['mailer']->send(array($htmlView, $plainView), $mailData, $callback);
	}

	protected function getExceptionBaseName($exception)
	{
		$exceptionName = get_class($exception);

		if (($pos = strrpos($exceptionName, '\\')) !== false) {
			$exceptionName = substr($exceptionName, ($pos + 1));
		}

		return $exceptionName;
	}
}
