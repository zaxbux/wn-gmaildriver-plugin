<?php

namespace Zaxbux\GmailMailerDriver;

use Log;
use System\Classes\PluginBase;
use Zaxbux\GmailMailerDriver\Models\Settings;
use Zaxbux\GmailMailerDriver\Classes\GoogleAPI;
use Zaxbux\GmailMailerDriver\Classes\GmailTransport;
use Zaxbux\GmailMailerDriver\Controllers\GoogleAuthRedirectURL;

class Plugin extends PluginBase {
	
	const MODE_GMAIL = 'gmail';

	/**
	 * {@inheritdoc}
	 */
	public function registerSettings() {
		return [
				'gmail' => [
						'label'       => 'Gmail Configuration',
						'description' => 'Configure sending with Gmail',
						'category'    => 'system::lang.system.categories.mail',
						'icon'        => 'icon-envelope',
						'class'       => 'Zaxbux\\GmailMailerDriver\\Models\\Settings',
						'order'       => 620,
						'keywords'    => 'google gmail mail email',
						'permissions' => ['zaxbux.gmailmailerdriver.access_settings']
				]
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function boot() {
		\Event::listen('backend.form.extendFields', function ($widget) {
			if (!$widget->getController() instanceof \System\Controllers\Settings) {
				return;
			}

			if (!$widget->model instanceof \System\Models\MailSetting) {
				return;
			}

			$field = $widget->getField('send_mode');
			$field->options(array_merge($field->options(), [self::MODE_GMAIL => 'Gmail']));

			$widget->addTabFields([
					'gmail_settings_link' => [
							'type' => 'partial',
							'path' => '~/plugins/zaxbux/gmailmailerdriver/partials/_gmail_settings_link.htm',
							'tab' => 'system::lang.mail.general',
							'trigger' => [
									'action' => 'show',
									'field' => 'send_mode',
									'condition' => 'value[' . self::MODE_GMAIL . ']'
							]
					]
			]);
		});

		\Event::listen('backend.form.extendFields', function ($widget) {
			if (!$widget->getController() instanceof \System\Controllers\Settings) {
				return;
			}

			if (!$widget->model instanceof Settings) {
				return;
			}

			$widget->addFields([
					'_auth_redirect_uri' => [
							'type' => 'partial',
							'path' => '~/plugins/zaxbux/gmailmailerdriver/partials/_google_api_redirect_uri.htm',
							'span' => 'right'
					]
			]);
			$widget->getField('_auth_redirect_uri')->value = (new GoogleAuthRedirectURL)->actionUrl('');

			if ($credentials = Settings::instance()->credentials) {

				$client = GoogleAPI::getClient($credentials, Settings::get(Settings::TOKEN_FIELD));

				try {
					$isAuthorized = $client->isAccessTokenExpired() && !$client->getRefreshToken();

					if ($isAuthorized) {
						$authUrl = $client->createAuthUrl();
					}
				} catch (\InvalidArgumentException $ex) {
					Log::alert($ex);

					$widget->addFields([
							'_error' => [
									'type' => 'partial',
									'path' => '~/plugins/zaxbux/gmailmailerdriver/partials/_google_api_error.htm'
							]
					]);
					$widget->getField('_error')->value = $ex->getMessage();

					return;
				}

				// If there is no previous token or it's expired, request authorization from the user.
				if ($isAuthorized) {
					$widget->addFields([
							'_authorize' => [
									'type' => 'partial',
									'path' => '~/plugins/zaxbux/gmailmailerdriver/partials/_google_api_authorize.htm'
							]
					]);
					$widget->getField('_authorize')->value = $authUrl;
				} else {
					// Tell user that authorization was successful
					$widget->addFields([
							'_authorized' => [
									'type' => 'partial',
									'path' => '~/plugins/zaxbux/gmailmailerdriver/partials/_google_api_authorized.htm'
							]
					]);
				}
			}
		});

		\App::extend('swift.transport', function (\Illuminate\Mail\TransportManager $manager) {
			return $manager->extend(self::MODE_GMAIL, function () {
				$client = GoogleAPI::getClient(Settings::instance()->credentials, Settings::get(Settings::TOKEN_FIELD));

				// Refresh access token as needed
				if ($client->isAccessTokenExpired()) {
					if ($client->getRefreshToken()) {
						$client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());

						Settings::set(Settings::TOKEN_FIELD, $client->getAccessToken());
					} else {
						throw new \Exception('Cannot send email. Gmail API not authorized.');
					}
				}

				return new GmailTransport($client);
			});
		});
	}
}
