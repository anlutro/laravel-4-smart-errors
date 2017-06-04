<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   l4-smart-errors
 */

namespace anlutro\L4SmartErrors;

use Carbon\Carbon;
use Illuminate\Config\Repository;
use Illuminate\Filesystem\Filesystem;

class ReportThrottler
{
	protected $config;
	protected $files;
	protected $maxAgeSeconds;

	public function __construct(
		Repository $config,
		Filesystem $files,
		$maxAgeSeconds = null
	) {
		$this->config = $config;
		$this->files = $files;
		$this->maxAgeSeconds = is_int($maxAgeSeconds) ? $maxAgeSeconds :
			$this->config->get('smarterror::throttle-age', 600);
	}

	/**
	 * Determine if an exception should be reported or not.
	 *
	 * @param  \Exception $exception
	 *
	 * @return boolean
	 */
	public function shouldReport($exception)
	{
		// if app.debug is true, reporting is unnecessary
		if ($this->config->get('app.debug') === true) {
			return false;
		}

		$path = $this->config->get('smarterror::storage-path');

		// create a basic hash of the exception. this should include the stack
		// trace and message, making it more or less a unique identifier
		$string = $exception->getMessage() . $exception->getCode()
			. $exception->getTraceAsString();

		$hash = base64_encode($string);

		// if the file exists, read from it and check if the hash of the current
		// exception is the same as the previous one.
		if ($this->files->exists($path)) {
			// read the json file into an associative array
			$data = json_decode($this->files->get($path), true);

			// don't check age if the file is empty
			if ($data) {
				$age = $this->getPreviousExceptionAge($data, $hash);
				return $age === false || $age > $this->maxAgeSeconds;
			}
		}

		// if the file is writeable, write the current exception hash into it.
		if ($this->pathIsWriteable($path)) {
			$data = array('previous' => array(
				'hash' => $hash,
				'time' => Carbon::now()->timestamp,
			));
			$this->files->put($path, json_encode($data));
		}

		return true;
	}

	/**
	 * Get the age in seconds of the previous time a given exception was
	 * handled, if possible.
	 *
	 * @param  array  $data The parsed JSON from the storage file.
	 * @param  string $hash The hash of the exception being handled.
	 *
	 * @return int|false    Returns false if age is indeterminable.
	 */
	protected function getPreviousExceptionAge(array $data, $hash)
	{
		// if any data is missing, age can't be determined
		if (!isset($data['previous']) || !isset($data['previous']['hash'])) {
			return false;
		}

		// if the hash does not equal, age does not matter
		if ($data['previous']['hash'] != $hash) {
			return false;
		}

		// if the time data is not set, age can't be determined
		if (!isset($data['previous']['time'])) {
			return false;
		}

		// all preconditions are OK, so calculate the time
		$age = Carbon::now()->timestamp - $data['previous']['time'];
		return (int) $age;
	}

	/**
	 * Determine if a path is writeable or not.
	 *
	 * @param  string $path
	 *
	 * @return boolean
	 *
	 * @throws \InvalidArgumentException
	 */
	protected function pathIsWriteable($path)
	{
		if ($this->files->isDirectory($path)) {
			throw new \InvalidArgumentException("$path is a directory");
		}

		// if the file exists, simply check if it is writeable
		if ($this->files->isFile($path) && $this->files->isWritable($path)) {
			return true;
		}

		// if not, check if the directory of the path is writeable
		$dir = dirname($path);

		if ($this->files->isDirectory($dir) && $this->files->isWritable($dir)) {
			return true;
		}

		return false;
	}
}
