<?php

declare(strict_types=1);

namespace Zaxbux\GmailDriver\Controllers;


use Google\Client;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Backend\Classes\Controller;
use Backend\Facades\Backend;
use Winter\Storm\Support\Facades\Flash;
use Winter\Storm\Support\Facades\Input;
use Zaxbux\GmailDriver\Classes\GoogleClientConfig;

/**
 * Handles Google OAuth 2.0 server response code.
 * 
 * @package Zaxbux\GmailDriver\Controllers
 */
class Auth extends Controller
{
	public $suppressView = true;

	public $requiredPermissions = ['system.manage_mail_settings'];

	public function index()
	{
		// Ensure that the state token (CSRF) matches
		if (!$this->verifyCsrfToken()) {
			return Response::make(Lang::get('system::lang.page.invalid_token.label'), 403);
		}

		if ($error = Input::get('error')) {
			if ($error == 'access_denied') {
				// The user denied the auth request
				Flash::error(Lang::get('zaxbux.gmaildriver::lang.error.oauth.access_denied'));
			} else {
				// Unknown error
				Flash::error(Lang::get('zaxbux.gmaildriver::lang.error.oauth_request_error', ['code' => $error]));
			}
		} else if ($code = Input::get('code')) {
			try {
				$this->fetchAccessToken($code);
			} catch (\Throwable $exception) {
				Log::error($exception);
				Flash::error($exception->getMessage());
			}
		} else {
			return Response::make('Missing parameters', 400);
		}

		return Backend::redirect('system/settings/update/winter/system/mail_settings');
	}

	/**
	 * {@inheritdoc}
	 */
	protected function verifyCsrfToken()
	{
		$token = Input::get('state');

		if (!strlen($token) || !strlen(Session::token())) {
			return false;
		}

		return hash_equals(
			Session::token(),
			$token
		);
	}

	private function fetchAccessToken(string $code): void
	{
		$client = new Client((new GoogleClientConfig)->get());

		// Exchange authorization code for refresh and access tokens
		$token = $client->fetchAccessTokenWithAuthCode($code);

		// Store the token
		if (isset($token['refresh_token'])) {
			GoogleClientConfig::setAccessToken($token);
		}
	}
}
