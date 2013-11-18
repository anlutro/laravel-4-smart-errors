<?php
/**
 * Laravel 4 Smart Errors
 *
 * @author    Andreas Lutro <anlutro@gmail.com>
 * @license   http://opensource.org/licenses/MIT
 * @package   Laravel 4 Smart Errors
 */

return array(
	// The email the error reports will be sent to.
	'dev-email' => '',

	// send an email even if mail.pretend == true
	'force-email' => false,

	// The error handler email view. Leave as null for default
	'error-email-view' => null,
	'error-email-view-plain' => null,

	// The alert log handler email view. Leave as null for default
	'alert-email-view' => null,
	'alert-email-view-plain' => null,

	// The view for generic errors (uncaught exceptions). Leave as null for default
	'error-view' => null,

	// The view for 404 errors. Leave as null for default
	'missing-view' => null,

	// The PHP date() format that should be used. Leave as null for default
	// Default: Y-m-d H:i:s e
	'date-format' => null,
);
