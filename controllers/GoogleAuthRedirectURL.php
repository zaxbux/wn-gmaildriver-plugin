<?php

namespace Zaxbux\Gmailmailerdriver\Controllers;

use Log;
use Flash;
use Input;
use Response;
use Redirect;
use Backend;
use Backend\Classes\Controller;
use Zaxbux\GmailMailerDriver\Classes\GoogleAPI;

class GoogleAuthRedirectURL extends Controller {

	public $requiredPermissions = ['zaxbux.gmailmailerdriver.access_settings'];

	public function index() {
		try {
			$authCode = Input::get('code');
			
			$googleAPI = new GoogleAPI();
			$googleAPI->authorize($authCode);
		} catch (\Exception $ex) {
			Flash::error('Error authorizing Gmail Driver plugin: ' . $ex->getMessage());
			Log::error($ex);
		}

		return Backend::redirect('system/settings/update/zaxbux/gmailmailerdriver/gmail');
	}
}