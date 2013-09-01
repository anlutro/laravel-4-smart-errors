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
	'dev_email' => '',

	// send an email even if mail.pretend == true
	'force_email' => false,

	// The error handler email view. Leave as null for default
	'email_view' => null,
	'email_view_plain' => null,

	// The alert log handler email view. Leave as null for default
	'alert_email_view' => null,
	'alert_email_view_plain' => null,

	// The view for generic errors (uncaught exceptions). Leave as null for default
	'error_view' => null,

	// The view for 404 errors. Leave as null for default
	'missing_view' => null,

	// The PHP date() format that should be used. Leave as null for default
	// Default: Y-m-d H:i:s e
	'date_format' => null,
);
