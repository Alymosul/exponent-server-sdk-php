<?php
namespace ExponentPhpSDK;

use ExponentPhpSDK\Exceptions\ExpoRegistrarException;

class ExpoRegistrar
{
    /**
     * Repository that manages the storage and retrieval
     *
     * @var ExpoRepository
     */
    private $repository;

    /**
     * ExpoRegistrar constructor.
     *
     * @param ExpoRepository $repository
     */
    public function __construct(ExpoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Registers the given token for the given interest
     *
     * @param $interest
     * @param $token
     *
     * @throws ExpoRegistrarException
     *
     * @return string
     */
    public function registerInterest($interest, $token)
    {
        if (! $this->isValidExpoPushToken($token)) {
            throw ExpoRegistrarException::invalidToken();
        }

        $stored = $this->repository->store($interest, $token);

        if (!$stored) {
            throw ExpoRegistrarException::couldNotRegisterInterest();
        }

        return $token;
    }

    /**
     * Gets the tokens of the interests
     *
     * @param array $interests
     *
     * @throws ExpoRegistrarException
     *
     * @return array
     */
    public function getInterests(array $interests): array
    {
        $tokens = [];

        foreach ($interests as $interest) {
            $token = $this->repository->retrieve($interest);

            if (!is_null($token)) {
                $tokens[] = $token;
            }
        }

        if (empty($tokens)) {
            throw ExpoRegistrarException::emptyInterests();
        }

        return $tokens;
    }

    /**
     * Determines if a token is a valid Expo push token
     *
     * @param string $token
     *
     * @return bool
     */
    private function isValidExpoPushToken(string $token)
    {
        return  substr($token, 0, 18) ===  "ExponentPushToken[" && substr($token, -1) === ']';
    }
}
