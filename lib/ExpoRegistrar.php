<?php
namespace ExponentPhpSDK;

use ExponentPhpSDK\Database\MysqlConnection;
use ExponentPhpSDK\Exceptions\ExpoRegistrarException;
use ExponentPhpSDK\Repositories\ExpoMysqlDriver;
use ExponentPhpSDK\Repositories\ExpoFileDriver;

class ExpoRegistrar
{
    /**
     * The current registered driver.
     *
     * @var string
     */
    private $driver;

    /**
     * Repository that manages the storage and retrieval
     *
     * @var ExpoRepository
     */
    private $repository;

    /**
     * ExpoRegistrar constructor.
     *
     * @param string $driver
     */
    public function __construct(string $driver)
    {
        $this->driver = $driver;
        $this->repository = $this->getRepository($driver);
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
        if (! is_string($interest)) {
            throw ExpoRegistrarException::invalidInterest();
        }

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
     * Removes token of a given interest
     *
     * @param $interest
     * @param $token
     *
     * @throws ExpoRegistrarException
     *
     * @return bool
     */
    public function removeInterest($interest, $token = null)
    {
        if (! is_string($interest)) {
            throw ExpoRegistrarException::invalidInterest();
        }

        if (!$this->repository->forget($interest, $token)) {
            throw ExpoRegistrarException::couldNotRemoveInterest();
        }

        return true;
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
            $retrieved = $this->repository->retrieve($interest);

            if (!is_null($retrieved)) {
                if(is_string($retrieved)) {
                    $tokens[] = $retrieved;
                }

                if(is_array($retrieved)) {
                    foreach($retrieved as $token) {
                        if(is_string($token)) {
                            $tokens[] = $token;
                        }
                    }
                }
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

    private function getRepository(string $driver)
    {
        switch ($driver) {
            case 'file':
                return new ExpoFileDriver();
            case 'mysql':
                return new ExpoMysqlDriver(new MysqlConnection());
        }
    }

    public function getDriver()
    {
        return $this->driver;
    }
}
