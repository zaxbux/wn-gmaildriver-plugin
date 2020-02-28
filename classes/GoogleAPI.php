<?php

namespace Zaxbux\GmailMailerDriver\Classes;

use Log;
use Config;
use Zaxbux\GmailMailerDriver\Models\Settings;
use Google_Client;
use Google_Service_Gmail;

class GoogleAPI {

	/**
	 * The Google API Client object
	 * @var Google_Client
	 */
	public $client;

	/**
	 * Google API authorization status
	 * @var bool
	 */
	private $authorized = false;

	/**
	 * Google Gmail API Service
	 * @var Google_Service_Gmail
	 */
	private $gmailService;

	public function __construct() {
		$authConfig  = $this->getAuthConfig();
		$accessToken = Settings::get(Settings::TOKEN_FIELD);
		
		$this->client = new Google_Client();
		$this->client->setApplicationName('October CMS Gmail Driver by Zaxbux'); // Used in the request User-Agent header

		$defaultScopes = [ Google_Service_Gmail::GMAIL_SEND ];
		$this->client->setScopes(Config::get('zaxbux.gmailmailerdriver::google.scopes', $defaultScopes));

		if ($authConfig) {
			$this->client->setAuthConfig($authConfig);
			$this->client->setAccessType('offline');
			$this->client->setPrompt('select_account consent');
		}

		// Load the previously authorized token, if it exists
		if ($accessToken) {
			$this->client->setAccessToken($accessToken);
		}

		if ($this->client->isAccessTokenExpired()) {
			if ($this->client->getRefreshToken()) {
				$this->client->fetchAccessTokenWithRefreshToken($this->client->getRefreshToken());

				// Save access token and refresh token
				Settings::set(Settings::TOKEN_FIELD, $this->client->getAccessToken());
			} else {
				Log::alert('Gmail Driver refresh token is invalid. Please re-authorize to continue sending emails.');
			}
		}

		$this->authorized = !$this->client->isAccessTokenExpired();
	}

	/**
	 * Get an instance of the Gmail API service
	 * @return Google_Service_Gmail
	 */
	public function getServiceGmail() {
		if (!$this->gmailService) {
			$this->gmailService = new Google_Service_Gmail($this->client);
		}

		return $this->gmailService;
	}

	/**
	 * Get an instance of the Gmail SendAs API service
	 * @return Google_Service_Gmail_SendAs
	 */
	public function getServiceGmailSendAs() {
		return $this->getServiceGmail()->users_settings_sendAs->get('me', $this->getServiceGmail()->users->getProfile('me')->getEmailAddress());
	}

	/**
	 * Exchange an authorization code for an access token
	 * @param $authCode string The authorization code received from Google
	 * @return bool
	 */
	public function authorize(string $authCode) {
		$accessToken = $this->client->fetchAccessTokenWithAuthCode($authCode);

		// Check to see if there was an error.
		if (array_key_exists('error', $accessToken)) {
			throw new \Exception(join(', ', $accessToken));
		}

		// Save the access token
		Settings::set(Settings::TOKEN_FIELD, $accessToken);

		return $this->authorized = !$this->client->isAccessTokenExpired();
	}

	/**
	 * Get stored OAuth credentials
	 * @return array
	 */
	public function getAuthConfig() {
		$file = Settings::instance()->credentials;

		if (!$file) {
			return;
		}

		return json_decode($file->getContents(), true);
	}

	/**
	 * Check if the refresh and access tokens are valid
	 * @return bool
	 */
	public function isAuthorized() {
		return $this->authorized;
	}
}