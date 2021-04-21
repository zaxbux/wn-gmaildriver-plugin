<?php

namespace Zaxbux\GmailDriver\ReportWidgets;

use Backend;
use Zaxbux\GmailDriver\Classes\GoogleAPI;

class AuthorizationStatus extends \Backend\Classes\ReportWidgetBase {
	public function render() {

		$googleAPI = new GoogleAPI();

		if (!$googleAPI->isConfigured()) {
			$this->vars['pluginSettingsURL'] = Backend::url('system/settings/update/zaxbux/gmaildriver/gmail');
			return $this->makePartial('configure');
		}

		if ($googleAPI->isAuthorized()) {
			return $this->makePartial('authorized');
		} else {
			$this->vars['googleAuthURL'] = $googleAPI->client->createAuthUrl();
			return $this->makePartial('unauthorized');
		}
	}
}
