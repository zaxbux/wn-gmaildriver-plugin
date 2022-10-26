<?php

namespace Zaxbux\GmailDriver;

use Backend\Classes\FormTabs;
use Backend\Widgets\Form;
use Google\Client;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use System\Classes\PluginBase;
use System\Models\MailSetting;
use Winter\Storm\Exception\ApplicationException;
use Zaxbux\GmailDriver\Classes\GmailApiTransport;
use Zaxbux\GmailDriver\Classes\GoogleClientConfig;

class Plugin extends PluginBase
{

	public const MODE_GMAIL = 'gmail';

	/**
	 * @var bool Plugin requires elevated permissions. Required for using the gmail driver to restore user passwords.
	 */
	public $elevated = true;

	/**
	 * {@inheritdoc}
	 */
	public function boot()
	{
		$this->registerMailTransport();

		MailSetting::extend(function (\Winter\Storm\Database\Model $model) {
			// Add validation rules
			$model->rules['gmail_client_id'] = ['required_with:gmail_client_secret'];
			$model->rules['gmail_client_secret'] = ['required_with:gmail_client_id'];

			// Revoke tokens when system mail settings are reset to default
			$model->bindEvent('model.beforeDelete', function () {
				if ($token = GoogleClientConfig::getAccessToken()) {
					$client = new Client((new GoogleClientConfig)->get());

					try {
						$client->setAccessToken($token);
						$client->revokeToken();
					} catch (\Throwable $ex) {
						Log::error($ex);
					}
				}
			});

			$model->bindEvent('model.form.filterFields', function (Form $formWidget, $fields, string $context) use ($model) {
				// Enable the widget's authorization button once the client ID/secret are configured
				if (GoogleClientConfig::isAppConfigSet()) {
					$fields->_gmail_authorize->disabled = false;
				} else {
					$fields->_gmail_authorize->disabled = !(strlen($fields->gmail_client_id->value) > 0 && strlen($fields->gmail_client_secret->value));
				}
			});
		});

		Event::listen('backend.form.extendFields', function (Form $widget) {
			if (!$widget->getController() instanceof \System\Controllers\Settings) {
				return;
			}

			if (!$widget->model instanceof \System\Models\MailSetting) {
				return;
			}

			// Add Gmail as transport option
			$sendModeField = $widget->getField('send_mode');
			$sendModeField->options(array_merge($sendModeField->options(), [static::MODE_GMAIL => 'Gmail']));

			// Temporarily remove the send test message button
			$sendTestButton = $widget->getField('_send_test');
			$widget->getTab(FormTabs::SECTION_PRIMARY)->removeField('_send_test');

			$this->addMailSettingFields($widget);

			// Add the send test message button back
			$widget->getTab(FormTabs::SECTION_PRIMARY)->addField('_send_test', $sendTestButton, 'system::lang.mail.general');
		});
	}

	private function registerMailTransport()
	{
		Mail::extend(static::MODE_GMAIL, function (array $mailerConfig = []) {
			if (!GoogleClientConfig::getAccessToken()) {
				throw new ApplicationException('Access token missing');
			}

			$client = new Client((new GoogleClientConfig($mailerConfig))->get());
			try {
				$client->setAccessToken(GoogleClientConfig::getAccessToken());
				if ($client->isAccessTokenExpired()) {
					GoogleClientConfig::setAccessToken($client->fetchAccessTokenWithRefreshToken());
				}
			} catch (\Throwable $exception) {
				Log::error($exception);
				throw new ApplicationException($exception->getMessage(), $exception->getCode(), $exception);
			}

			return new GmailApiTransport($client);
		});
	}

	private function addMailSettingFields(Form $widget): void
	{
		$tabConfig = [
			'tab' => 'system::lang.mail.general',
			'trigger' => [
				'action'    => 'show',
				'field'     => 'send_mode',
				'condition' => 'value[' . static::MODE_GMAIL . ']',
			],
		];
		$widget->addTabFields([
			'_gmail_redirect_uri' => [
				'type' => \Zaxbux\GmailDriver\FormWidgets\GoogleOAuthRedirectURI::class,
				'label' => 'zaxbux.gmaildriver::lang.settings.auth_redirect_uri.label',
				'comment' => 'zaxbux.gmaildriver::lang.settings.auth_redirect_uri.comment',
				'commentHtml' => true,
				...$tabConfig,
			],
			'gmail_client_id' => [
				'type' => 'text',
				'label' => 'zaxbux.gmaildriver::lang.settings.client_id.label',
				'comment' => 'zaxbux.gmaildriver::lang.settings.client_id.comment',
				'commentHtml' => true,
				...$tabConfig,
			],
			'gmail_client_secret' => [
				'type' => 'sensitive',
				'label' => 'zaxbux.gmaildriver::lang.settings.client_secret.label',
				'comment' => 'zaxbux.gmaildriver::lang.settings.client_secret.comment',
				'commentHtml' => true,
				...$tabConfig,
			],
			'_gmail_authorize' => [
				'type' => \Zaxbux\GmailDriver\FormWidgets\GoogleApiAuthorize::class,
				'disabled' => !GoogleClientConfig::isAppConfigSet(),
				'dependsOn' => ['gmail_client_id', 'gmail_client_secret'],
				...$tabConfig,
			],
		]);
	}
}
