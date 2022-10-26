<?php

namespace Zaxbux\GmailDriver\FormWidgets;

use Backend\Classes\FormWidgetBase;
use Zaxbux\GmailDriver\Controllers\Auth;

/**
 * Google OAuth Redirect URI widget.
 *
 * Renders a field that can be copied to the clipboard.
 */
class GoogleOAuthRedirectURI extends FormWidgetBase
{
	/**
	 * @inheritDoc
	 */
	protected $defaultAlias = 'google_oauth_redirect_uri';

	/**
	 * @inheritDoc
	 */
	public function render()
	{
		$this->prepareVars();

		return $this->makePartial('googleoauthredirecturi');
	}

	/**
	 * Prepares the view data for the widget partial.
	 */
	public function prepareVars()
	{
		$this->vars['value'] = $this->getLoadValue();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getLoadValue()
	{
		return (new Auth)->actionUrl('');
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSaveValue($value)
	{
		return null;
	}

	/**
	 * @inheritDoc
	 */
	protected function loadAssets()
	{
		$this->addCss('css/googleoauthredirecturi.css', 'core');
		$this->addJs('js/googleoauthredirecturi.js', 'core');
	}
}
