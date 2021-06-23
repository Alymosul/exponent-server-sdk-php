# exponent-server-sdk-php
Server-side library for working with Expo push notifications using PHP

[![Latest Stable Version](https://poser.pugx.org/alymosul/exponent-server-sdk-php/v/stable)](https://packagist.org/packages/alymosul/exponent-server-sdk-php)
[![License](https://poser.pugx.org/alymosul/exponent-server-sdk-php/license)](https://packagist.org/packages/alymosul/exponent-server-sdk-php)
[![Total Downloads](https://poser.pugx.org/alymosul/exponent-server-sdk-php/downloads)](https://packagist.org/packages/alymosul/exponent-server-sdk-php)

# Usage
- Require the package in your project
```bash
composer require alymosul/exponent-server-sdk-php
```
- In a php file
```php
    require_once __DIR__.'/vendor/autoload.php';
    
    $channelName = 'news';
    $recipient= 'ExponentPushToken[unique]';
    
    // You can quickly bootup an expo instance
    $expo = \ExponentPhpSDK\Expo::normalSetup();
    
    // Subscribe the recipient to the server
    $expo->subscribe($channelName, $recipient);
    
    // Build the notification data
    $notification = ['body' => 'Hello World!'];
    
    // Notify an interest with a notification
    $expo->notify([$channelName], $notification);
 ```
Data can be added to notifications by providing it as a JSON object. For example:
```php
// Build the notification data
$notification = ['body' => 'Hello World!', 'data'=> json_encode(array('someData' => 'goes here'))];
```

# Channel name

You can use channels to send a notification to only one user, or to a group of users:

## One recipient

In order to target one recipient (and avoid sending a notification to the wrong recipient), use a channel name specific to each user:

```php
$channelName = 'user_528491';
$recipient = 'ExponentPushToken[unique]';

// …

// Subscribe the recipient to the server
$expo->subscribe($channelName, $recipient);

// …

// Notify an interest with a notification, only one recipient will receive it
$expo->notify([$channelName], $notification);
```

## Several recipients

Declare a channel name that will be shared between the recipients:

```php
$channelName = 'group_4815';

$recipient1 = 'ExponentPushToken[unique1]';
$recipient2 = 'ExponentPushToken[unique2]';

// …

// Subscribe the recipients to the server
$expo->subscribe($channelName, $recipient1);
$expo->subscribe($channelName, $recipient2);

// …

// Notify an interest with a notification, the 2 recipients will receive it
$expo->notify([$channelName], $notification);
```

```php
// Build the notification data
$notification = ['body' => 'Hello World!', 'data'=> json_encode(array('someData' => 'goes here'))];
```

# Additional security

If you set up enhanced security in your Expo Dashboard (as described [here](https://docs.expo.io/push-notifications/sending-notifications/#additional-security)), you will need to attach an authorization token to each push request:

```php
    // ...
    
    // Bootup an expo instance
    $expo = \ExponentPhpSDK\Expo::normalSetup();
    
    // Fetch your access token from where you stored it
    $accessToken = 'your_expo_access_token';
    
    // The access token will be attached to every push request you make hereafter
    $expo->setAccessToken($accessToken);
    
    // Notify an interest with a notification
    $expo->notify([$channelName], $notification);
 ```

# TODO
- Need to create tests    

# Laravel driver
- There's an expo notifications driver built for laravel apps that's ready to use, you can find it here.. https://github.com/Alymosul/laravel-exponent-push-notifications
