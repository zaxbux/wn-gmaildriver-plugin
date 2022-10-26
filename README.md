# Gmail Mailer Driver

![GitHub](https://img.shields.io/github/license/zaxbux/wn-gmaildriver-plugin)
![Packagist PHP Version Support](https://img.shields.io/packagist/php-v/zaxbux/wn-gmaildriver-plugin)
![GitHub tag (latest by date)](https://img.shields.io/github/v/tag/zaxbux/wn-gmaildriver-plugin)
![Packagist Version](https://img.shields.io/packagist/v/zaxbux/wn-gmaildriver-plugin)


Send email with Gmail using this driver plugin for Winter CMS.

> **Note:** You must use version [1.0.15](https://github.com/zaxbux/wn-gmaildriver-plugin/releases/tag/v1.0.15) with Winter CMS < v1.2.

> **Note:** You must use at least version [1.1.0](https://github.com/zaxbux/wn-gmaildriver-plugin/releases/tag/v1.0.15) with Winter CMS >= v1.2. This version of Winter has introduced changes to the way mail transports are configured (See *Installing* for more info).

## Installing

```bash
composer require zaxbux/wn-gmaildriver-plugin
```

In your Winter CMS `config/mail.php`, add the **gmail** transport to the **mailers** array:

```php
'mailers' => [

	// ...

	'gmail' => [
		'transport' => 'gmail',
	],

	// ...
],
```

## Requirements

* A Gmail account. Google Workspace (G Suite) accounts are also supported, if your administrator has enabled access.
* Gmail API credentials (see below).

## Plugin Settings

The plugin is configured in your backend settings. Change the mail method to ``Gmail`` in **Mail configuration** and configure your API credentials. Obtain API credentials from the [Google Cloud Console](https://console.cloud.google.com/apis/credentials).


### Obtaining API Keys

[Read instructions on obtaining API keys](https://github.com/zaxbux/wn-gmaildriver-plugin/wiki/Documentation#obtaining-api-keys)

### Testing delivery
To make sure everything is working, try test sending a mail template to yourself.

### Revoking access
In case you want to revoke access, click the **Revoke Authorization** or **Reset to default** buttons on the *Gmail configuration* page. This will delete the credentials and access tokens. You may also want to [remove app access on your Google account](https://support.google.com/accounts/answer/3466521).

## Important Notes
* The Gmail API will only send emails as the account which you granted access with, it is currently not possible to send email as another user.
* The Gmail API has [sending limits](https://developers.google.com/gmail/api/reference/quota) for [free (consumer)](https://support.google.com/mail/answer/22839#zippy=you-have-reached-a-limit-for-sending-mail) and [paid (Workspace)](https://support.google.com/a/answer/166852#limits) accounts.
* You can send email using another email address under your control, using an alias. [Learn more about configuring sending aliases in this Google Support article](https://support.google.com/mail/answer/22370). Change the *Sender email* in **Mail configuration** to match your alias. The *Sender Name* does not have to match and can be whatever you choose.
* The authorization tokens expire if not used for 6 months or if you change your Google account password.

## Advanced Usage

This section is for advanced users.

### Providing credentials

To provide the OAuth credentials without entering them through the backend UI, you can add them in the `config/mail.php` file. This is useful if you want to provide credentials using an environment variable. By default, the contents of the `GOOGLE_APPLICATION_CREDENTIALS_JSON` environment variable will be used.

Example:

```php
'mailers' => [
	'gmail' => [
		'transport' => 'gmail',
		// ...

		'client_id' => '...',      // Your OAuth client ID
		'client_secret' => '...',  // Your OAuth client secret

		// ...
	],
],
```

### Authentication Scopes

To change the authentication scopes (permissions requested from the user), you can override the default scopes in the `config/mail.php` file. Add scopes to the `mail/mailers.gmail.scopes` array. For the plugin to function correctly, the `gmail.send` scope is required (this scope is always included by the plugin). [Gmail Auth Scopes](https://developers.google.com/gmail/api/auth/scopes)

Example:

```php
'mailers' => [

	// ...

	'gmail' => [
		'transport' => 'gmail',
		
		// ...

		'scopes' => [
			\Google_Service_Gmail::GMAIL_READONLY,
		],
	],

	// ...
],
```

## Change Log

* **1.1.0** - Support for Winter CMS 1.2, Laravel 9, and PHP 8.
* **1.0.16** - Fixed localization key issue and updated dependencies.
* **1.0.15** - Fixed migration to Winter ([@mjauvin](https://github.com/mjauvin))
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
