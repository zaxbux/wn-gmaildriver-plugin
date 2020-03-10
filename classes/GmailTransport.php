<?php

namespace Zaxbux\GmailMailerDriver\Classes;

use Log;
use ApplicationException;
use Swift_Transport;
use Swift_Mime_SimpleMessage;
use Swift_Mime_ContentEncoder_Base64ContentEncoder;
use Swift_Events_EventListener;
use Google_Service_Gmail_Message;
use Google_Http_MediaFileUpload;
use Zaxbux\GmailMailerDriver\Classes\GoogleAPI;

class GmailTransport implements Swift_Transport {
    
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
    public function isStarted() {
        return true;
    }


    /**
     * Stub since Gmail API is stateless
     */
    public function start() {
        return true;
    }

    /**
     * Stub since Gmail API is stateless
     */
    public function stop() {
        return true;
    }

    /**
     * Stub since Gmail API is stateless
     */
    public function ping() {
        return true;
    }

    /**
     * Not implemented
     */
    public function registerPlugin(Swift_Events_EventListener $plugin) {}


    /**
     * Send an email
     */
    public function send(Swift_Mime_SimpleMessage $message, &$failedRecipients = null) {
        try {
            // Use a resumable upload for large mails
            $gmailMessage = new Google_Service_Gmail_Message();

            // Set client to deferred mode
            $this->googleAPI->client->setDefer(true);

            // Resumable upload
            $usersMessages = $this->googleAPI->getServiceGmail()->users_messages;
            $gmailMessage = $usersMessages->send('me', $gmailMessage, ['uploadType' => Google_Http_MediaFileUpload::UPLOAD_RESUMABLE_TYPE]);

            // Use chunks of 3 MB
            $chunkSizeBytes = 3 * 1024 * 1024;
            $media = new Google_Http_MediaFileUpload(
                $this->googleAPI->client,
                $gmailMessage,
                'message/rfc822',
                $message->toString(),
                true,
                $chunkSizeBytes
            );
            $media->setFileSize(strlen($message->toString()));

            $status = false;
            while (! $status) {
                $status = $media->nextChunk();
            }

            // Reset client to immediately send requests
            $this->googleAPI->client->setDefer(false);
        } catch (\Google_Service_Exception $ex) {
            Log::alert($ex);
            throw new ApplicationException('Failed to send email. Check event log for more info. Message: '.json_decode($ex->getMessage(), true)['error']['message']);
        }
    }
}
