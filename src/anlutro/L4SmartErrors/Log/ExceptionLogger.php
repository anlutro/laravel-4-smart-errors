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

class ExceptionLogger
{
	protected $logger;
	protected $contextCollector;

	public function __construct(
		LoggerInterface $logger,
		ContextCollector $contextCollector
	) {
		$this->logger = $logger;
		$this->contextCollector = $contextCollector;
	}

	public function log($exception)
	{
		$logstr = "Uncaught $exception";

		$context = $this->contextCollector->getContext();

		$this->logger->error($logstr, $context);
	}
}
