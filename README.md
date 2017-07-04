# exponent-server-sdk-php
Server-side library for working with Expo push notifications using PHP

# Usage
- Require the package in your project

        composer require alymosul/exponent-server-sdk-php
        
- In a php file
        
        require_once __DIR__.'/vendor/autoload.php';
        
        $interestDetails = ['unique identifier', 'ExpoPushToken[unique]'];
        
        // Register the interest in the server
        $registrar = new \ExponentPhpSDK\ExpoRegistrar(new \ExponentPhpSDK\ExpoFileDriver()));
        $registrar->registerInterest($interestDetails[0], $interestDetails[1]);
        
        // You can quickly bootup an expo instance with the above registrar
        $expo = new \ExponentPhpSDK\Expo($registrar);
        
        // Build the notification data
        $notification = ['body' => 'Hello World!];
        
        // Notify an interest with a notification
        $expo->notify($interesDetails[0], $notification);
        
# TODO
- Need to create tests        
