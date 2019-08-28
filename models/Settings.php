<?php

namespace Zaxbux\GmailMailerDriver\Models;

use Log;
use October\Rain\Database\Model;
use Zaxbux\GmailMailerDriver\Classes\GoogleAPI;

class Settings extends Model
{
    const TOKEN_FIELD = 'token';

    public $implement      = ['System.Behaviors.SettingsModel'];
    public $settingsCode   = 'zaxbux_gmailmailerdriver_settings';
    public $settingsFields = 'fields.yaml';

    public $attachOne = [
			'credentials' => [
				'System\\Models\\File',
				'public' => false,
				'delete' => true
				]
		];

		/**
		 * Revoke the token with Google before deleting the credentials
		 */
		public function resetDefault() {
			if ($credentials = self::instance()->credentials) {
				$client = GoogleAPI::getClient($credentials, self::get(self::TOKEN_FIELD));

				// Refresh access token as needed
				if ($client->isAccessTokenExpired()) {
					if ($client->getRefreshToken()) {
						$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
					}
				}

				// Revoke authorization with Google
				if ($client->revokeToken($client->getAccessToken())) {
					Log::info('Gmail Mailer Driver: Auth token revoked');
				}
			}

			parent::resetDefault();
		}
}
