<?php

namespace Zaxbux\GmailMailerDriver\Classes;

use Google_Client;
use Google_Service_Gmail;

class GoogleAPI {
	public static function getClient($auth, $token = null) {
		if (!$auth) {
			throw new \Exception("Gmail credentials missing.");
		}

		if ($auth instanceof \System\Models\File) {
			$auth = $auth->getContents();
		}

		$client = new Google_Client();
		$client->addScope(Google_Service_Gmail::GMAIL_SEND);
		$client->setAuthConfig(json_decode($auth, true));
		$client->setAccessType('offline');
		$client->setPrompt('select_account consent');

		if ($token) {
			$client->setAccessToken($token);
		}

		return $client;
	}
}