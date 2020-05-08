<?php

namespace Zaxbux\GmailMailerDriver\Models;

use Log;
use October\Rain\Database\Model;
use Zaxbux\GmailMailerDriver\Classes\GoogleAPI;

class Settings extends Model {
	const TOKEN_FIELD  = 'token';
	const CONFIG_FIELD = 'credentials';

	public $implement      = ['System.Behaviors.SettingsModel'];
	public $settingsCode   = 'zaxbux_gmailmailerdriver_settings';
	public $settingsFields = 'fields.yaml';

	public $attachOne = [
		'credentials' => [
			'System\\Models\\File',
			'public' => false,
			'delete' => true,
		],
	];

	/**
	 * Revoke the token with Google before deleting the credentials
	 */
	public function resetDefault() {
		try {
			$googleAPI = new GoogleAPI();

			if ($googleAPI->isAuthorized()) {
				// Revoke authorization with Google
				if ($googleAPI->client->revokeToken($googleAPI->client->getAccessToken())) {
					Log::info('Gmail Mailer Driver: Auth token revoked');
				}
			}
		} catch (\Exception $ex) {
			Log::alert($ex);
		}

		parent::resetDefault();
	}
}
