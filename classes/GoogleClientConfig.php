<?php

namespace Zaxbux\GmailDriver\Classes;

use Illuminate\Support\Arr;
use System\Models\MailSetting;
use Winter\Storm\Support\Facades\Config;
use Zaxbux\GmailDriver\Controllers\Auth;

final class GoogleClientConfig
{

	/** @var array */
	private $config;

	public function __construct($config = null)
	{
		if (is_null($config)) {
			$config = Config::get('mail.mailers.gmail');
		}

		$this->config = $config;
	}

	public function get()
	{
		return [
			'application_name' => Arr::get($this->config, 'application_name', Config::get('app.name')),
			'client_id' => self::getMailSetting('gmail_client_id', Arr::get($this->config, 'client_id')),
			'client_secret' => self::getMailSetting('gmail_client_secret', Arr::get($this->config, 'client_secret')),
			'redirect_uri' => (new Auth)->actionUrl(''),
			'access_type' => 'offline',
			'scopes' => [\Google\Service\Gmail::GMAIL_SEND, ...Arr::get($this->config, 'scopes', [])],
		];
	}

	public static function getAccessToken()
	{
		return self::getMailSetting('gmail_access_token');
	}

	public static function setAccessToken($token)
	{
		return MailSetting::set('gmail_access_token', $token);
	}

	public static function isAppConfigSet(): bool
	{
		return Config::has('mail.mailers.gmail.client_id') && Config::has('mail.mailers.gmail.client_secret');
	}

	private static function getMailSetting($key, $default = null)
	{
		$value = MailSetting::get($key, $default);
		if (empty($value)) {
			return $default;
		}

		return $value;
	}
}
