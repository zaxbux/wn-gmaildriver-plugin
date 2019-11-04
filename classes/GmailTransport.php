<?php

namespace Zaxbux\GmailMailerDriver\Classes;

use Log;
use ApplicationException;
use Zaxbux\GmailMailerDriver\Classes\GoogleAPI;
use Swift_Transport;
use Swift_Mime_SimpleMessage;
use Swift_Events_EventListener;
use Swift_Mime_ContentEncoder_Base64ContentEncoder;
use Google_Service_Gmail_Message;

class GmailTransport implements Swift_Transport
{
    
    /**
     * Google API client
     * @var GoogleAPI
     */
	private $googleAPI;
		
		public function __construct() {
			$this->googleAPI = new GoogleAPI();

			if (!$this->googleAPI->isAuthorized()) {
				throw new \Exception('Cannot send email. Gmail API not authorized.');
			}
		}

    /**
     * Stub since Gmail API is stateless
     */
    public function isStarted()
    {
        return true;
    }


    /**
     * Stub since Gmail API is stateless
     */
    public function start()
    {
        return true;
    }

    /**
     * Stub since Gmail API is stateless
     */
    public function stop()
    {
        return true;
    }

    /**
     * Stub since Gmail API is stateless
     */
    public function ping()
    {
        return true;
    }

    /**
     * Not implemented
     */
    public function registerPlugin(Swift_Events_EventListener $plugin) {}

    /**
     * Converts a Swift Simple Message to a base64url format string
     * @param $message Swift_Mime_SimpleMessage
     * @return string
     */
    private static function base64url(Swift_Mime_SimpleMessage $message) {
        $b64Encoder = new Swift_Mime_ContentEncoder_Base64ContentEncoder();
        return rtrim(strtr($b64Encoder->encodeString($message), '+/', '-_'), '='); // Converts base64 into base64url
    }

    /**
     * Send an email
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {
        try {
            $encodedMessage = $this::base64url($message);

            $gmailMessage = new Google_Service_Gmail_Message();
            $gmailMessage->setRaw($encodedMessage);

            $gmailMessage = $this->googleAPI->getServiceGmail()->users_messages->send('me', $gmailMessage); // 'me' references the currently authenticated user
        } catch (\Google_Service_Exception $ex) {
            Log::alert("Error sending Gmail message:\n".$ex->getMessage());
            throw new ApplicationException('Failed to send email. Check event log for more info. '.json_decode($ex->getMessage(), true)['error']['message']);
        }
    }
}
