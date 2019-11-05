<?php

namespace Zaxbux\GmailMailerDriver\ReportWidgets;

use Backend;
use Zaxbux\GmailMailerDriver\Classes\GoogleAPI;

class AuthorizationStatus extends \Backend\Classes\ReportWidgetBase {
    public function render() {

        $googleAPI = new GoogleAPI();

        if (!$googleAPI->getAuthConfig()) {
            $this->vars['pluginSettingsURL'] = Backend::url('system/settings/update/zaxbux/gmailmailerdriver/gmail');
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