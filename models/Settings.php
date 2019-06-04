<?php

namespace Zaxbux\GmailMailerDriver\Models;

use October\Rain\Database\Model;

class Settings extends Model
{
    const TOKEN_FIELD = 'token';

    public $implement      = ['System.Behaviors.SettingsModel'];
    public $settingsCode   = 'zaxbux_gmailmailerdriver_settings';
    public $settingsFields = 'fields.yaml';

    public $attachOne = [
			'credentials' => [
				'System\\Models\\File',
				'public' => false
				]
		];
}
