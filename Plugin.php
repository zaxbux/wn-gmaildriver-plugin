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
	 * @var bool Plugin requires elevated permissions. Required for using the gmail driver to restore user passwords.
	 */
	public $elevated = true;

	/**
	 * {@inheritdoc}
	 */
	public function registerSettings() {
		return [
			'gmail' => [
				'label'       => 'zaxbux.gmailmailerdriver::lang.settings.label',
				'description' => 'zaxbux.gmailmailerdriver::lang.settings.description',
				'category'    => 'system::lang.system.categories.mail',
				'icon'        => 'icon-envelope',
				'class'       => 'Zaxbux\\GmailMailerDriver\\Models\\Settings',
				'order'       => 620,
				'keywords'    => 'google gmail mail email',
				'permissions' => ['zaxbux.gmailmailerdriver.access_settings'],
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerPermissions() {
		return [
			'zaxbux.gmailmailerdriver.access_settings' => [
				'label' => 'zaxbux.gmailmailerdriver::lang.permissions.access_settings.label',
				'tab'   => 'zaxbux.gmailmailerdriver::lang.permissions.access_settings.tab',
			],
		];
	}

	/**
	 * {@inheritdoc}
	 */
	public function registerReportWidgets() {
		return [
			'Zaxbux\\GmailMailerDriver\\ReportWidgets\\AuthorizationStatus' => [
				'label'       => 'zaxbux.gmailmailerdriver::lang.widgets.authorizationstatus.label',
				'context'     => 'dashboard',
				'permissions' => ['zaxbux.gmailmailerdriver.access_settings'],
			],
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

			$sendModeField = $widget->getField('send_mode');
			$sendModeField->options(array_merge($sendModeField->options(), [self::MODE_GMAIL => 'Gmail']));

			$widget->addTabFields([
				'gmail_settings_link' => [
					'type'    => 'partial',
					'path'    => '~/plugins/zaxbux/gmailmailerdriver/partials/_gmail_settings_link.htm',
					'tab'     => 'system::lang.mail.general',
					'trigger' => [
						'action'    => 'show',
						'field'     => 'send_mode',
						'condition' => 'value[' . self::MODE_GMAIL . ']',
					],
				],
			]);

			// Gmail ignored these settings, so hide them from the user when Gmail is selected
			$widget->getField('sender_name')->trigger = [
				'action'    => 'hide',
				'field'     => 'send_mode',
				'condition' => 'value[' . self::MODE_GMAIL . ']',
			];
			$widget->getField('sender_email')->trigger = [
				'action'    => 'hide',
				'field'     => 'send_mode',
				'condition' => 'value[' . self::MODE_GMAIL . ']',
			];
		});

		\Event::listen('backend.form.extendFields', function ($widget) {
			if (!$widget->getController() instanceof \System\Controllers\Settings) {
				return;
			}

			if (!$widget->model instanceof Settings) {
				return;
			}

			$widget->getField('_auth_redirect_uri')->value = (new GoogleAuthRedirectURL)->actionUrl('');

			try {
				$googleAPI = new GoogleAPI();

				if ($googleAPI->isAuthorized()) {
					// Tell user that authorization was successful
					$widget->addFields([
						'_authorized' => [
							'type' => 'partial',
							'path' => '~/plugins/zaxbux/gmailmailerdriver/partials/_google_api_authorized.htm',
						],
					]);
				} else {
					// Credentials must be present in order for an auth URL to be generated
					if ($googleAPI->getAuthConfig()) {
						// If there is no previous token or it's expired, request authorization from the user.
						$widget->addFields([
							'_authorize' => [
								'type' => 'partial',
								'path' => '~/plugins/zaxbux/gmailmailerdriver/partials/_google_api_unauthorized.htm'
							]
						]);
						$widget->getField('_authorize')->value = $googleAPI->client->createAuthUrl();
					}
				}
			} catch (\Exception $ex) {
				Log::alert($ex);

				$widget->addFields([
					'_error' => [
						'type' => 'partial',
						'path' => '~/plugins/zaxbux/gmailmailerdriver/partials/_google_api_error.htm',
					],
				]);
			}

			// Show "review me" callout if not hidden
			if (!Settings::get('_review_hidden')) {
				$widget->addFields([
					'_review_hidden' => [
						'type' => 'partial',
						'path' => '$/zaxbux/gmailmailerdriver/partials/_review.htm',
					],
				]);
			}
		});

		\App::extend('swift.transport', function (\Illuminate\Mail\TransportManager $manager) {
			return $manager->extend(self::MODE_GMAIL, function () {
				return new GmailTransport();
			});
		});

		Settings::extend(function($model) {
			$model->bindEvent('model.beforeSave', function() use ($model) {
				// Convert hidden input value to boolean
				$model->setSettingsValue('_review_hidden', $model->getSettingsValue('_review_hidden', false) ? true : false);
			});
		});
	}
}
