# Gmail Mailer Driver for October CMS

Send email with Gmail using this driver plugin for OctoberCMS.

## Requirements

* A Gmail account (GSuite accounts are supported)
* Gmail API credentials

## Plugin Settings

The plugin is configured in your October CMS backend settings. Change the mail method to ``Gmail`` in **Mail configuration**, save the settings and go to **Gmail configuration** to upload your API credentials. Obtain API credentials from the [Google Cloud Console](https://console.cloud.google.com/apis/credentials).

### Obtaining API Keys
1. Go to the [Google Cloud Console](https://console.cloud.google.com/apis/credentials) and create a new project.
2. Give your project a name, and click **Create**. Wait for your project to be created.
3. Click the **Create credentials** dropdown and choose **OAuth client ID**
4. You may be asked to configure the **OAuth consent screen**
    1. Set the **Application name**
    2. Add your domain to the **Autorized domains** list
    3. Configure any other option as you like. [More info here](https://support.google.com/cloud/answer/6158849).
5. Select **Web application** as the *Application type*, and give it a name.
6. Copy the **Authorized Redirect URI** from the backend Gmail configuration page.
7. Add that URI as an **Authorized redirect URI** and click **Create**
8. Look for your new credential in the **OAuth 2.0 Client IDs** table, and click the *download* button to download your credentials in JSON format.
9. Click on **Library** in the sidebar and search for "Gmail". Click **Enable** to enable access to the Gmail API.
10. Upload the file you downloaded to the backend *Gmail configuration* page and save the settings. Reload the page to show the **Authorize** button.
11. Click the **Authorize** button to open the Google consent page. Continue to select the account you want to send email with and consent to sending email on your behalf.
    * You may encounter a screen that says "This app isn't verified". This is referring to the OAuth consent screen that you created, and can be bypassed by clicking *Advanced* and then *Go to \<domain\> (unsafe)*.

### Testing delivery
To make sure everything is working, try test sending a mail template to yourself.

### Revoking access
In case you want to revoke access, click **Reset to default** on the *Gmail configuration* page. This will delete the credentials and access tokens. You may also want to [remove app access on your Google account](https://support.google.com/accounts/answer/3466521).

## Important Notes
* The Gmail API will only send emails as the account which you granted access with, it is currently not possible to send email as another user.

## Change Log

* 1.0.3 - Fixed logic issues, app is removed from user account when settings are reset. Updated dependencies.
* 1.0.2 - Fixed errors that occur when invalid credentials are supplied.
* 1.0.1 - First version.

## TODO

All done!
