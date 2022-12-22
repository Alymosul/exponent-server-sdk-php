<?php

namespace ExponentPhpSDK;

use ExponentPhpSDK\Exceptions\ExpoException;
use ExponentPhpSDK\Exceptions\UnexpectedResponseException;
use ExponentPhpSDK\Repositories\ExpoFileDriver;

class Expo
{
    /**
     * The Expo Api Url that will receive the requests
     */
    const EXPO_API_URL = 'https://exp.host/--/api/v2/push/send';

    /**
     * cURL handler
     *
     * @var null|resource
     */
    private $ch = null;

    /**
     * The registrar instance that manages the tokens
     *
     * @var ExpoRegistrar
     */
    private $registrar;
    
    /** 
     * @var string|null
     */
    private $accessToken = null;

    /**
     * Expo constructor.
     *
     * @param ExpoRegistrar $expoRegistrar
     */
    public function __construct(ExpoRegistrar $expoRegistrar)
    {
        $this->registrar = $expoRegistrar;
    }

    /**
     * Creates an instance of this class with the normal setup
     * It uses the ExpoFileDriver as the repository.
     *
     * @return Expo
     */
    public static function normalSetup()
    {
        return new self(new ExpoRegistrar(new ExpoFileDriver()));
    }

    /**
     * Subscribes a given interest to the Expo Push Notifications.
     *
     * @param $interest
     * @param $token
     *
     * @return string
     */
    public function subscribe($interest, $token)
    {
        return $this->registrar->registerInterest($interest, $token);
    }

    /**
     * Unsubscribes a given interest from the Expo Push Notifications.
     *
     * @param $interest
     * @param $token
     *
     * @return bool
     */
    public function unsubscribe($interest, $token = null)
    {
        return $this->registrar->removeInterest($interest, $token);
    }
    
    /**
     * @param string|null $accessToken
     */
    public function setAccessToken(string $accessToken = null) {
        $this->accessToken = $accessToken;
    }

    /**
     * Send a notification via the Expo Push Notifications Api.
     *
     * @param array $interests
     * @param array $data
     * @param bool $debug
     *
     * @throws ExpoException
     * @throws UnexpectedResponseException
     *
     * @return array|bool
     */
    public function notify(array $interests, array $data, $debug = false)
    {
        $postData = [];

        if (count($interests) == 0) {
            throw new ExpoException('Interests array must not be empty.');
        }

        // Gets the expo tokens for the interests
        $recipients = $this->registrar->getInterests($interests);

        foreach ($recipients as $token) {
            $postData[] = $data + ['to' => $token];
        }

        $ch = $this->prepareCurl();

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_UNICODE));

        $response = $this->executeCurl($ch);

        // If the notification failed completely, throw an exception with the details
        if ($debug && $this->failedCompletely($response, $recipients)) {
            throw ExpoException::failedCompletelyException($response);
        }

        return $response;
    }

    /**
     * Determines if the request we sent has failed completely
     *
     * @param array $response
     * @param array $recipients
     *
     * @return bool
     */
    private function failedCompletely(array $response, array $recipients)
    {
        $numberOfRecipients = count($recipients);
        $numberOfFailures = 0;

        foreach ($response as $item) {
            if ($item['status'] === 'error') {
                $numberOfFailures++;
            }
        }

        return $numberOfFailures === $numberOfRecipients;
    }

    /**
     * Sets the request url and headers
     *
     * @throws ExpoException
     *
     * @return null|resource
     */
    private function prepareCurl()
    {
        $ch = $this->getCurl();

        $headers = [
                'accept: application/json',
                'content-type: application/json',
        ];

        if ($this->accessToken) {
            $headers[] = sprintf('Authorization: Bearer %s', $this->accessToken);
        }

        // Set cURL opts
        curl_setopt($ch, CURLOPT_URL, self::EXPO_API_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $ch;
    }

    /**
     * Handle with unexpected response error
     *
     * @throws UnexpectedResponseException
     *
     * @return null|resource
     */
    private function handleWithUnexpectedResponse($response) {
        if (is_array($response) && isset($response['body'])) {
            $errors = json_decode($response['body'])->errors ?? [];

            return $errors[0]->message ?? null;
        }

        return null;
    }

    /**
     * Get the cURL resource
     *
     * @throws ExpoException
     *
     * @return null|resource
     */
    public function getCurl()
    {
        // Create or reuse existing cURL handle
        $this->ch = $this->ch ?? curl_init();

        // Throw exception if the cURL handle failed
        if (!$this->ch) {
            throw new ExpoException('Could not initialise cURL!');
        }

        return $this->ch;
    }

    /**
     * Executes cURL and captures the response
     *
     * @param $ch
     *
     * @throws UnexpectedResponseException
     *
     * @return array
     */
    private function executeCurl($ch)
    {
        $response = [
            'body' => curl_exec($ch),
            'status_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE)
        ];

        $responseData = json_decode($response['body'], true)['data'] ?? null;

        if (! is_array($responseData)) {
            throw new UnexpectedResponseException(
                $this->handleWithUnexpectedResponse($response)
            );
        }

        return $responseData;
    }
}
