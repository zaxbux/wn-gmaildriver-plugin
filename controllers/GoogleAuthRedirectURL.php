<?php

namespace Zaxbux\Gmailmailerdriver\Controllers;

use Input;
use Response;
use Redirect;
use Backend;
use Backend\Classes\Controller;
use Zaxbux\GmailMailerDriver\Models\Settings;
use Zaxbux\GmailMailerDriver\Classes\GoogleAPI;

class GoogleAuthRedirectURL extends Controller {

	public $requiredPermissions = ['zaxbux.gmailmailerdriver.access_settings'];

	public function index() {
		try {
			$client = GoogleAPI::getClient(Settings::instance()->credentials);

			$accessToken = $client->fetchAccessTokenWithAuthCode(Input::get('code'));
			
			Settings::set(Settings::TOKEN_FIELD, $accessToken);
		} catch (\Exception $ex) {
			return new Response($ex->getMessage());
		}

		return Redirect::to(Backend::url('system/settings/update/zaxbux/gmailmailerdriver/gmail'));
	}
}