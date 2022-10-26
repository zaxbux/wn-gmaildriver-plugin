<?php

namespace Zaxbux\GmailDriver\Classes;

use Google\Client;
use Google\Http\MediaFileUpload;
use Google\Service\Exception as GoogleServiceException;
use Google\Service\Gmail;
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Mailer\Transport\AbstractTransport;
use Symfony\Component\Mailer\SentMessage;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Mime\RawMessage;
use Winter\Storm\Exception\ApplicationException;

final class GmailApiTransport extends AbstractTransport
{
	private const CHUNK_SIZE_BYTES = 3 * 1024 * 1024;

	/**
	 * Google API client
	 * 
	 * @var \Google\Client
	 */
	private $client;

	/**
	 * {@inheritdoc}
	 * 
	 * @param array $config 
	 * @param array $accessToken
	 */
	public function __construct(Client $client, EventDispatcherInterface $dispatcher = null, LoggerInterface $logger = null)
	{
		parent::__construct($dispatcher, $logger);

		$this->client = $client;
	}

	/**
	 * Get the string representation of the transport.
	 *
	 * @return string
	 */
	public function __toString(): string
	{
		return sprintf('gmail+api://%s', $this->client->getClientId());
	}

	/**
	 * {@inheritDoc}
	 */
	protected function doSend(SentMessage $sentMessage): void
	{
		$message = $sentMessage->getOriginalMessage();

		try {
			$this->doSendApi($message);
		} catch (GoogleServiceException $exception) {
			$json = @json_decode($exception->getMessage(), true);

			if ($json['error']['code'] == 401 && $json['error']['status'] == 'UNAUTHENTICATED') {
				// The refresh token expired
				Log::alert('Gmail Driver authorization expired', $json);
				GoogleClientConfig::setAccessToken(null);
			} else {
				Log::error($exception);
			}

			// Throw an exception in case the user is sending a test message so that the error message is displayed.
			throw new ApplicationException($json['error']['message'], $json['error']['code'], $exception);
		} catch (\Throwable $exception) {
			Log::error($exception);
			throw new ApplicationException('Gmail Driver Exception: ' . $exception->getMessage());
		}
	}

	private function doSendApi(RawMessage $message): void
	{
		// Set client to deferred mode
		// This causes the call to the gmail service to return an object that implements RequestInterface, which must be passed to MediaFileUpload in order to perform a resumable upload.
		$this->client->setDefer(true);

		// Resumable upload
		$gmailService = new Gmail($this->client);

		/** @var \Psr\Http\Message\RequestInterface $messageSendRequest */
		$messageSendRequest = $gmailService->users_messages->call('send', [[
			'userId' => 'me',
			'uploadType' => MediaFileUpload::UPLOAD_RESUMABLE_TYPE,
		]]);

		// Raw message
		$data = $message->toString();

		$media = new MediaFileUpload(
			$this->client,
			$messageSendRequest,
			'message/rfc822',
			$data,
			true,
			static::CHUNK_SIZE_BYTES
		);
		$media->setFileSize(strlen($data));

		$status = false;
		while (!$status) {
			$status = $media->nextChunk();
		}

		// Reset client to immediately send requests
		$this->client->setDefer(false);
	}
}
