<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Mail;

use anlutro\L4SmartErrors\AppInfoGenerator;
use anlutro\L4SmartErrors\Presenters\LogContextPresenter;
use anlutro\L4SmartErrors\Traits\ConfigCompatibilityTrait;
use Illuminate\Foundation\Application;
use Illuminate\Mail\Message;

class AlertLogMailer
{
	use ConfigCompatibilityTrait;

	protected $app;
	protected $message;
	protected $context;
	protected $appInfo;

	public function __construct(
		Application $app,
		$message,
		LogContextPresenter $context,
		AppInfoGenerator $appInfo
	) {
		$this->app = $app;
		$this->message = $message;
		$this->context = $context;
		$this->appInfo = $appInfo;
	}

	public function send($email)
	{
		if ($this->getConfig('smarterror::force-email')) {
			$this->app['config']->set('mail.pretend', false);
		}

		$mailData = array(
			'logmsg'  => $this->message,
			'context' => $this->context,
			'info'    => $this->appInfo,
		);

		$env = $this->app->environment();
		$cc = $this->app['config']->get('smarterror::cc-email');
		$subject = "[$env] Alert logged - ";
		$subject .= $this->app['request']->root() ?: $this->getConfig('app.url');
		$htmlView = $this->getConfig('smarterror::alert-email-view') ?: 'smarterror::alert-email';
		$plainView = $this->getConfig('smarterror::alert-email-view-plain') ?: 'smarterror::alert-email-plain';

		$callback = function(Message $msg) use($email, $subject, $cc) {
			$msg->to($email)->subject($subject);
			if (isset($cc) && $cc) {
				$msg->cc($cc);
			}
		};

		$this->app['mailer']->send(array($htmlView, $plainView), $mailData, $callback);
	}
}
