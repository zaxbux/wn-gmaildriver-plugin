<?php

use October\Rain\Database\Updates\Migration;
use Zaxbux\GmailMailerDriver\Models\Settings;

class UpdateSettings_2021_04_08_000001 extends Migration {
	public function up() {
		// Convert settings file into encrypted JSON
		if($file = Settings::instance()->credentials) {
			$config = \json_decode($file->getContents(), true);

			// Adapted from: https://github.com/googleapis/google-api-php-client/blob/c925552c84ca5cf02e36b83e72b5371ec3bea391/src/Client.php#L970
			$key = isset($config['installed']) ? 'installed' : 'web';
			if (isset($config[$key])) {
				// old-style
				Settings::set([
					'client_id'     => $config[$key]['client_id'],
					'client_secret' => encrypt($config[$key]['client_secret']),
				]);
			} else {
				// new-style
				Settings::set([
					'client_id'     => $config['client_id'],
					'client_secret' => encrypt($config['client_secret']),
				]);
			}

			
		}
	}

	public function down() {
		// We didn't delete the credentials file, nothing to downgrade.
	}
}