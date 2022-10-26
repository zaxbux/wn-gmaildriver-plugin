<?php

namespace Zaxbux\GmailDriver\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Google\Client;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use System\Controllers\Settings;
use Winter\Storm\Support\Facades\Flash;
use Zaxbux\GmailDriver\Classes\GoogleClientConfig;

/**
 * Clipboard Copy widget.
 *
 * Renders a password field that can be copied to the clipboard.
 */
class GoogleApiAuthorize extends FormWidgetBase
{

	/**
	 * @inheritDoc
	 */
	protected $defaultAlias = 'google_api_authorize';

	/**
	 * @inheritDoc
	 */
	public function init()
	{
		$this->fillFromConfig([]);
	}

	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$this->prepareVars();

		return $this->makePartial('googleapiauthorize');
	}

	/**
	 * Prepares the view data for the widget partial.
	 */
	public function prepareVars()
	{
		$this->vars['isAuthorized'] = !empty(GoogleClientConfig::getAccessToken());
	}

	public function onAuthorize()
	{
		// Save the mail settings
		if ($this->controller instanceof Settings) {
			$this->controller->update_onSave('winter', 'system', 'mail_settings');
		}

		$client = new Client((new GoogleClientConfig)->get());
		if ($client->getClientId() && $client->getClientSecret()) {
			$client->setState(Session::token());
			$client->setPrompt('select_account consent');

			return Redirect::to($client->createAuthUrl());
		} else {
			Flash::error(Lang::get('zaxbux.gmaildriver::lang.formwidgets.google_oauth.missing_client_id_secret'));
		}
	}

	public function onRevoke()
	{
		if ($token = GoogleClientConfig::getAccessToken()) {
			$client = new Client((new GoogleClientConfig)->get());
			$client->setAccessToken($token);

			try {
				$client->revokeToken();
			} catch (\Throwable $ex) {
				Flash::error($ex->getMessage());
			} finally {
				GoogleClientConfig::setAccessToken(null);
			}
		}

		Flash::success(Lang::get('zaxbux.gmaildriver::lang.formwidgets.google_oauth.revoke_complete'));
		return Redirect::refresh();
	}
}
