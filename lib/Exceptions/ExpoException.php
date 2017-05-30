<?php

namespace ExponentPhpSDK\Exceptions;

class ExpoException extends \Exception
{
    /**
     * Formats the exception for a completely failed request
     *
     * @param $response
     *
     * @return static
     */
    public static function failedCompletelyException($response)
    {
        $message = '';
        foreach ($response as $key => $item) {
            if ($item['status'] === 'error') {
                $message .= $key == 0? "" : "\r\n";
                $message .= $item['message'];
            }
        }

        return new static($message);
    }
}
