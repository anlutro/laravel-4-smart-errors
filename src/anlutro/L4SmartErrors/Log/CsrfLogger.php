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

class CsrfLogger
{
	protected $logger;
	protected $contextCollector;
	protected $sessionToken;
	protected $inputToken;

	public function __construct(
		LoggerInterface $logger,
		ContextCollector $contextCollector,
		$sessionToken,
		$inputToken
	) {
		$this->logger = $logger;
		$this->contextCollector = $contextCollector;
		$this->sessionToken = $sessionToken;
		$this->inputToken = $inputToken;
	}

	public function log()
	{
		$logstr = "CSRF token mismatch - session value: {$this->sessionToken} - input value: {$this->inputToken}";
		$this->logger->warning($logstr, $this->contextCollector->getContext());
	}
}
