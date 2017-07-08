# exponent-server-sdk-php
Server-side library for working with Expo push notifications using PHP

# Usage
- Require the package in your project

        composer require alymosul/exponent-server-sdk-php
        
- In a php file
        
        require_once __DIR__.'/vendor/autoload.php';
        
        $interestDetails = ['unique identifier', 'ExpoPushToken[unique]'];
        
        // You can quickly bootup an expo instance
        $expo = \ExponentPhpSDK\Expo::normalSetup();
        
        // Subscribe the recipient to the server
        $expo->subscribe($interestDetails[0], $interestDetails[1]);
        
        // Build the notification data
        $notification = ['body' => 'Hello World!];
        
        // Notify an interest with a notification
        $expo->notify($interesDetails[0], $notification);
        
# TODO
- Need to create tests        
