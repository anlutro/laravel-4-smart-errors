<?php
namespace anlutro\L4SmartErrors\Log;

use Exception;
use Illuminate\Log\Writer as Logger;
use anlutro\L4SmartErrors\AppInfoGenerator;
use anlutro\L4SmartErrors\Presenters\InputPresenter;

class ExceptionLogger
{
	public function __construct(
		Logger $logger,
		AppInfoGenerator $appInfo,
		InputPresenter $input = null
	) {
		$this->logger = $logger;
		$this->appInfo = $appInfo;
		$this->input = $input;
	}

	public function log(Exception $exception)
	{
		$logstr = "Uncaught Exception (handled by L4SmartErrors)\n";

		$logstr .= $this->appInfo->renderCompact();

		if ($this->input) {
			$logstr .= "\nInput: " . $this->input->renderCompact();
		}

		$logstr .= "\n" . $exception;

		$this->logger->error($logstr);
	}
}
