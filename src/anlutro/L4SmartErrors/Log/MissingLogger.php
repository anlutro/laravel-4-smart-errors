<?php
namespace anlutro\L4SmartErrors\Log;

use Illuminate\Log\Writer as Logger;
use Illuminate\Http\Request;

class MissingLogger
{
	protected $logger;
	protected $request;

	public function __construct(
		Logger $logger,
		Request $request
	) {
		$this->logger = $logger;
		$this->request = $request;
	}

	public function log()
	{
		$url = $this->request->fullUrl();
		$referer = $this->request->header('referer') ?: 'none';

		$this->logger->warning("404 for URL $url -- Referer: $referer (handled by L4SmartErrors)");
	}
}
