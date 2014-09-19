<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors\Log;

use Psr\Log\LoggerInterface;
use anlutro\L4SmartErrors\AppInfoGenerator;

class CsrfLogger
{
	public function __construct(
		LoggerInterface $logger,
		AppInfoGenerator $appInfo
	) {
		$this->logger = $logger;
		$this->appInfo = $appInfo;
	}

	public function log()
	{
		$logstr = "CSRF token mismatch (handled by L4SmartErrors)\n";
		$logstr .= $this->appInfo->renderCompact();
		$this->logger->warning($logstr);
	}
}
