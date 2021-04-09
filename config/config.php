<?php

/*
 * WARNING!
 * 
 * This file can be overridden by plugin updates.
 * Copy this file to your global config directory instead: `config/zaxbux/gmailmailerriver/config.php`
 */

return [
	/**
	 * Configuration options related to the Google APIs
	 */
	'google' => [
		/**
		 * An array of auth scopes used with the Google API Client. Default: https://www.googleapis.com/auth/gmail.send
		 */
		//'scopes' => [
		//	\Google_Service_Gmail::GMAIL_SEND,
		//],

		/**
		 * The contents of the downloaded OAuth credentials JSON file, or an object containing 'client_id' and 'client_secret'
		 */
		'credentials' => env('GOOGLE_APPLICATION_CREDENTIALS_JSON'),
	],
];