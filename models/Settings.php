<?php

namespace Zaxbux\GmailDriver\Models;

use Log;
use Winter\Rain\Database\Model;
use Zaxbux\GmailDriver\Classes\GoogleAPI;

class Settings extends Model {
	use \Winter\Rain\Database\Traits\Validation;

	const TOKEN_FIELD  = 'token';
	const CONFIG_FIELD = 'credentials';

	public $implement = [
		\System\Behaviors\SettingsModel::class
	];

	public $settingsCode   = 'zaxbux_gmaildriver_settings';
	public $settingsFields = 'fields.yaml';

	public $attachOne = [
		'credentials' => [
			'System\\Models\\File',
			'is_public' => false,
			'delete' => true,
		],
	];

	public $rules = [
		'client_id' => ['required'],
		'client_secret' => ['required'],
	];

	public function beforeModelSave() {
		$this->fieldValues['client_secret'] = !empty($this->fieldValues['client_secret']) ? encrypt($this->fieldValues['client_secret']) : null;
	}

	public function afterModelFetch() {
		$this->fieldValues['client_secret'] = !empty($this->fieldValues['client_secret']) ? decrypt($this->fieldValues['client_secret']) : null;
	}

	/**
	 * Revoke the token with Google before deleting the credentials
	 */
	public function resetDefault() {
		try {
			$googleAPI = new GoogleAPI();

			// If we're still authorized, revoke app authorization with Google
			if ($googleAPI->isAuthorized()) {
				if ($googleAPI->client->revokeToken($googleAPI->client->getAccessToken())) {
					Log::info('Gmail Mailer Driver: Auth token revoked');
				}
			}
		} catch (\Exception $ex) {
			Log::alert($ex);
		}

		// This also will delete the credentials stored in the database
		parent::resetDefault();
	}
}
