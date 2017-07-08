<?php

namespace ExponentPhpSDK;

use ExponentPhpSDK\Exceptions\ExpoException;
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
    private $ch = null; // Curl handler

    /**
     * The registrar instance that manages the tokens
     *
     * @var ExpoRegistrar
     */
    private $registrar;

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
     *
     * @return bool
     */
    public function unsubscribe($interest)
    {
        return $this->registrar->removeInterest($interest);
    }

    /**
     * Send a notification via the Expo Push Notifications Api.
     *
     * @param $interests
     * @param array $data
     * @param bool $debug
     *
     * @throws ExpoException
     *
     * @return array|bool
     */
    public function notify($interests, array $data, $debug = false)
    {
        $postData = [];

        if (is_string($interests)) {
            $interests = [$interests];
        }

        if (count($interests) == 0) {
            throw new ExpoException('Interests array must not be empty.');
        }

        // Gets the expo tokens for the interests
        $recipients = $this->registrar->getInterests($interests);

        foreach ($recipients as $token) {
            $postData[] = $data + ['to' => $token];
        }

        $ch = $this->prepareCurl();

        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));

        $response = $this->executeCurl($ch);

        // If the notification failed completely, throw an exception with the details
        if (!$debug && $this->failedCompletely($response, $interests)) {
            throw ExpoException::failedCompletelyException($response);
        }

        return $response;
    }

    /**
     * Determines if the request we sent has failed completely
     *
     * @param $response
     * @param array $interests
     *
     * @return bool
     */
    private function failedCompletely($response, array $interests)
    {
        $numberOfInterests = count($interests);
        $numberOfFailures = 0;

        foreach ($response as $item) {
            if ($item['status'] === 'error') {
                $numberOfFailures++;
            }
        }

        return $numberOfFailures === $numberOfInterests;
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
        // Create or reuse existing cURL handle
        $this->ch = $this->ch?? curl_init();

        // Throw exception if the cURL handle failed
        if (!$this->ch) {
            throw new ExpoException('Could not initialise cURL!');
        }

        $ch = $this->ch;

        // Set cURL opts
        curl_setopt($ch, CURLOPT_URL, self::EXPO_API_URL);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'accept: application/json',
            'content-type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        return $ch;
    }

    /**
     * Executes cURL and captures the response
     *
     * @param $ch
     *
     * @return array
     */
    private function executeCurl($ch)
    {
        $response = [
            'body' => curl_exec($ch),
            'status_code' => curl_getinfo($ch, CURLINFO_HTTP_CODE)
        ];

        return json_decode($response['body'], true)['data'];
    }
}
