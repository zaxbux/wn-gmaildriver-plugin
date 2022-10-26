<?php return [
	'plugin' => [
		'name'        => 'Gmail Mail Driver',
		'description' => 'Send email with Gmail using this driver plugin.',
	],
	'settings' => [
		'auth_redirect_uri' => [
			'label' => 'Authorized Redirect URI',
			'comment' => 'This is used as a restriction when creating a new OAuth Client ID. <a href="https://console.cloud.google.com/apis/credentials" target="_blank" rel="noopener">Generate API credentials <i class="icon-external-link"></i></a>',
		],
		'client_id' => [
			'label'   => 'OAuth Client ID',
			'comment' => 'This value can also be set in your app configuration (<code>mail.mailers.gmail.client_id</code>).',
		],
		'client_secret' => [
			'label'   => 'OAuth Client Secret',
			'comment' => 'This value can also be set in your app configuration ( <code>mail.mailers.gmail.client_secret</code>).',
		],
	],
	'formwidgets' => [
		'google_oauth_redirect_uri' => [
			'copy' => 'Copy',
		],
		'google_oauth' => [
			'label' => 'Authorization',
			'comment' => 'You must give :app_name permission to send emails on your behalf using your Gmail account.',
			'button_authorize' => 'Grant Authorization',
			'authorize_confirm' => 'You will be redirected you to a Google consent screen where you will be prompted to authorize :app_name to send emails on your behalf.',
			'missing_client_id_secret' => 'The OAuth Client ID and OAuth Client secret are required.',
			'button_revoke' => 'Revoke Authorization',
			'revoke_confirm' => 'This will revoke your authorization to send emails using your Gmail account.',
			'revoke_complete' => 'Authorization revoked.',
		],
	],
	'error' => [
		'oauth' => [
			'access_denied' => 'Authorization request denied.',
		],
		'oauth_request_error' => 'Authorization request error: :code',
	],
];
