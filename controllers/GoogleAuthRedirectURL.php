<?php namespace Zaxbux\GmailDriver\Controllers;

use Log;
use Flash;
use Input;
use Response;
use Redirect;
use Backend;
use Backend\Classes\Controller;
use Zaxbux\GmailDriver\Classes\GoogleAPI;

class GoogleAuthRedirectURL extends Controller {

	public $requiredPermissions = ['zaxbux.gmaildriver.access_settings'];

	public function index() {
		try {
			$authCode = Input::get('code');
			
			$googleAPI = new GoogleAPI();
			$googleAPI->authorize($authCode);
		} catch (\Exception $ex) {
			Log::error($ex);
			Flash::error('Error authorizing Gmail Driver plugin: ' . $ex->getMessage());
		}

		return Backend::redirect('system/settings/update/zaxbux/gmaildriver/gmail');
	}
}
