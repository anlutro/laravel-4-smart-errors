<?php
namespace anlutro\L4SmartErrors\Mail;

use Illuminate\Foundation\Application;
use anlutro\L4SmartErrors\AppInfoGenerator;
use anlutro\L4SmartErrors\Presenters\LogContextPresenter;

class AlertLogMailer
{
	protected $app;
	protected $message;
	protected $context;
	protected $appInfo;

	public function __construct(Application $app, $message, LogContextPresenter $context, AppInfoGenerator $appInfo)
	{
		$this->app = $app;
		$this->message = $message;
		$this->context = $context;
		$this->appInfo = $appInfo;
	}

	public function send($email)
	{
		$mailData = array(
			'logmsg'  => $this->message,
			'context' => $this->context,
			'info'    => $this->appInfo,
		);

		$env = $this->app->environment();
		$subject = "[$env] Alert logged - ";
		$subject .= $this->app['request']->root() ?: $this->app['config']->get('app.url');
		$htmlView = $this->app['config']->get('smarterror::alert-email-view') ?: 'smarterror::alert-email';
		$plainView = $this->app['config']->get('smarterror::alert-email-view-plain') ?: 'smarterror::alert-email-plain';

		$callback = function($msg) use($email, $subject) {
			$msg->to($email)->subject($subject);
		};

		$this->app['mailer']->send(array($htmlView, $plainView), $mailData, $callback);
	}
}
