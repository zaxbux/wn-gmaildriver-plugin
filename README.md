# Gmail Mailer Driver

Send email with Gmail using this driver plugin for Winter CMS.

## Requirements

* A Gmail account. Google Workspace (G Suite) accounts are also supported, if your administrator has enabled access.
* Gmail API credentials (see below).

## Plugin Settings

The plugin is configured in your backend settings. Change the mail method to ``Gmail`` in **Mail configuration**, save the settings and go to **Gmail configuration** to upload your API credentials. Obtain API credentials from the [Google Cloud Console](https://console.cloud.google.com/apis/credentials).

### Obtaining API Keys

[Read instructions on obtaining API keys](https://github.com/zaxbux/wn-gmaildriver-plugin/wiki/Documentation#obtaining-api-keys)

### Testing delivery
To make sure everything is working, try test sending a mail template to yourself.

### Revoking access
In case you want to revoke access, click **Reset to default** on the *Gmail configuration* page. This will delete the credentials and access tokens. You may also want to [remove app access on your Google account](https://support.google.com/accounts/answer/3466521).

## Important Notes
* The Gmail API will only send emails as the account which you granted access with, it is currently not possible to send email as another user.
* You can send email using another email address you own, using an alias. [Learn more](https://support.google.com/mail/answer/22370). Change the *Sender name* and *Sender email* in **Mail configuration** to match your alias.

## Advanced Usage

This section is for advanced users.

**Note:** To avoid overwriting your custom configuration with plugin updates, copy the provided `config.php` to the Winter CMS config directory: `config/zaxbux/gmaildriver/config.php`.

### Providing credentials

To provide the OAuth credentials without uploading them through the backend UI, you can add them in the plugin's `config.php` file. This is useful if you want to provide credentials using an environment variable. By default, the contents of the `GOOGLE_APPLICATION_CREDENTIALS_JSON` environment variable will be used.

Example:

```php
    'credentials' => '{"web": {"client_id": "000000000000-aaaaaaaaaaaaaaaaaaaaaaaaaaaaaaaa.apps.googleusercontent.com", ... }}',
```

### Authentication Scopes

To change the authentication scopes (permissions requested from the user), you can override the default scopes in the plugin's `config.php` file. Add scopes to the `google.scopes` array. For the plugin to function correctly, the `gmail.send` scope is required. [Gmail Auth Scopes](https://developers.google.com/gmail/api/auth/scopes)

Example:

```php
    'scopes' => [
        \Google_Service_Gmail::GMAIL_SEND,
        \Google_Service_Gmail::GMAIL_READONLY,
    ],
```

## Change Log

* **1.0.14** - Removed requirement to upload JSON file, client secret is now encrypted in DB.
* **1.0.13** - Added Gmail alias documentation, added ability to pass credentials via environment vars.
* **1.0.12** - Improve UI.
* **1.0.11** - Increased max sending size to 35MB (encoded message size).
* **1.0.10** - Added ability to change auth scopes used with the Google API client.
* **1.0.9** - Improved localization.
* **1.0.8** - Plugin requires elevated permissions to function on restore password page.
* **1.0.7** - Added settings permissions
* **1.0.6** - Added authorization status dashboard widget.
* **1.0.5** - Cleaned up Gmail API authorization flow.
* **1.0.4** - Fixed issue where backend authorization status showed expired when it was authorized.
* **1.0.3** - Fixed logic issues, app is removed from user account when settings are reset. Updated dependencies.
* **1.0.2** - Fixed errors that occur when invalid credentials are supplied.
* **1.0.1** - First version.

## Acknowledgments

* [@alxy](https://github.com/alxy) - Increased max sending size to 35MB.