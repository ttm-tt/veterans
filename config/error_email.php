<?php
// Configuration for error email
// Must come after email, because settings require a valid email configuration

return [

// ErrorEmail
	'ErrorEmail' => [
		'email' => true,
		'toEmailAddress' => 'root@localhost',
		'emailLevels' => ['exception', 'error'],		
	],
	'Cache' => [
		'_error_email_' => [
			'className' => 'File',
			'prefix' => 'error_email_',
			'path' => CACHE . 'error_emails/',
			'duration' => '+5 minutes'
		]
	]
];
