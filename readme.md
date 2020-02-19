# Exponent Push Notification Server SDK
Server-side PHP library for working with Expo push notifications

[![Latest Version on Packagist](https://img.shields.io/packagist/v/subit/php-expo-sdk.svg?style=flat-square)](https://packagist.org/packages/subit/php-expo-sdk)
[![Software License](https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/subit/php-expo-sdk.svg?style=flat-square)](https://packagist.org/packages/subit/php-expo-sdk)

## Contents

- [Installation](#installation)
- [Usage](#usage-simple-example)
- [Expo methods](#expo-methods)
- [ExpoMessage options](#expomessage-options)


&nbsp;
## Installation

You can install the package via composer:
```
composer require subit-io/expo-server-sdk-php
```
&nbsp;
## Usage (simple example)

Create an new `Expo` SDK client

```
$expo = new Expo();
```

Create the `ExpoMessage` you want to send to the client

```
$message = ExpoMessage::create()
	->to('')
	->title('')
	...;
```

Then use the `Expo->sendPushNotification()` method to send the `ExpoMessage`

```
$expo->sendPushNotification($message)
```

That's it! The notification has now been sent to the Expo servers.

&nbsp;
## `Expo` methods

| `sendPushNotification(ExpoMessage)`        | Sends an `ExpoMessage`                                            |
|--------------------------------------------|-------------------------------------------------------------------|
| `sendPushNotifications([ExpoMessage])`     | Sends multiple `ExpoMessage`                                      |
| `getPushNotificationReceipt($ticketId)`    | Get delivery status for Android/iOS services (not client device)  |
| `getPushNotificationReceipts([$ticketId])` | Get delivery status for Android/iOS services (not client devices) |
| `isValidTokenFormat($token)`               | Check if an expo token is of a valid format                       |
| `isValidReceiptIdFormat($id)`              | Check if a receipt id is of a valid format                        |
| `chunkPushNotifications()`                 | Chunk notifications into approved request batch sizes             |
| `chunkPushNotificationReceiptIds`          | Chunk notification receipt ids into approved request batch sizes  |

&nbsp;
## `ExpoMessage` options

|     Property     | iOS/Android |   Type   |                           Description                          |
|:----------------:|:-----------:|:--------:|:--------------------------------------------------------------:|
|      `to()`      |     both    |  string  | An Expo push token specifying the recipient of this message    |
|   `jsonData()`   |     both    |  string  | A JSON object delivered to your app. It may be up to 4KiB      |
|     `title()`    |     both    |  string  | The title to display in the notification                       |
|     `body()`     |     both    |  string  | The message to display in the notification                     |
|      `ttl()`     |     both    |    int   | Seconds the message may be kept around for redelivery          |
|  `expiration()`  |     both    |    int   | UNIX epoch timestamp. Same effect as ttl. ttl takes precedence |
|   `priority()`   |     both    | Priority | The delivery priority of the message                           |
|   `subtitle()`   |     iOS     |  string  | The subtitle to display in the notification below the title    |
|  `enableSound()` |     iOS     |          | Play a sound when the recipient receives this notification     |
| `disableSound()` |     iOS     |          | Play no sound (default)                                        |
|     `badge()`    |     iOS     |    int   | Number to display in the badge on the app icon                 |
|   `channelId()`  |   Android   |  string  | Channel through which to display this notification             |

For a more detailed description, refer to the Expo documentation https://docs.expo.io/versions/latest/guides/push-notifications/#formats

&nbsp;
### To be implemented/reworked:

* Look into if requests should be run asynchronously
* Send request gzipped
* Make an ExpoMessage able to receive multiple tokens
