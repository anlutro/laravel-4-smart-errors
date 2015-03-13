<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

class L5ErrorHandler extends ErrorHandler
{
	protected $response;

	/**
	 * {@inheritdoc}
	 */
	public function report(Exception $exception)
	{
		if ($exception instanceof NotFoundHttpException) {
			$this->response = $this->handleMissing($exception);
		}

		if ($exception instanceof TokenMismatchException) {
			$this->response = $this->handleTokenMismatch($exception);
		}

		$this->response = parent::handleException($exception, $code);
	}

	public function respond(Exception $exception)
	{
		# code...
	}
}
