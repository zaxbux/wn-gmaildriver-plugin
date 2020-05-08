<?php return [
    'plugin' => [
        'name'        => 'Gmail Mailer Driver',
        'description' => 'Send email with Gmail using this driver plugin for OctoberCMS.'
    ],
    'permissions' => [
        'access_settings' => [
            'label' => 'Manage Gmail Settings',
            'tab'   => 'Gmail Driver'
        ],
    ],
    'settings' => [
        'label'       => 'Gmail Configuration',
        'description' => 'Configure sending with Gmail',
        'field'       => [
            'credentials' => [
                'label'   => 'Gmail API Credentials',
                'comment' => 'Gmail API credentials are required to send emails. <a href="https://console.cloud.google.com/apis/credentials" target="_blank">Generate API credentials</a>'
            ],
            'auth_redirect_uri' => [
                'label' => 'Authorized Redirect URI',
                'comment' => 'This Authorized Redirect URI is used as a restriction when creating a new OAuth Client ID.',
            ],
        ],
    ],
    'partials' => [
        'gmail_settings_link' => [
            'comment' => 'Additional Gmail settings must be configured before you can send emails.',
            'button'  => 'Configure Gmail',
        ],
        'google_api_error' => [
            'label'   => 'Error!',
            'comment' => 'There was a problem with granting access to the Gmail API. Please create new credentials and try again. Check the error log for more information.',
        ],
        'review' => [
            'header' => 'Did you find this plugin useful?',
            'link' => 'Rate it and leave a review!',
        ]
    ],
    'widgets' => [
        'authorizationstatus' => [
            'label'        => 'Gmail Driver Authorization Status',
            'authorized'   => [
                'label'   => 'Authorized',
                'comment' => 'October CMS is authorized to send emails via Gmail.',
                'button'  => 'Test Delivery',
            ],
            'configure'    => [
                'label'   => 'Setup Required',
                'comment' => 'Gmail Driver requires credentials to send emails.',
                'button'  => 'Configure Gmail Driver',
            ],
            'unauthorized' => [
                'label'   => 'Unauthorized',
                'comment' => 'October CMS requires authorization to send emails via Gmail.',
                'button'  => 'Authorize',
            ],
        ],
    ],
];