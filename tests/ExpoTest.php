<?php

namespace Subit\ExpoSdk\Tests;

use GuzzleHttp\Client;
use Mockery as m;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Subit\ExpoSdk\Exceptions\MissingExponentTokenException;
use Subit\ExpoSdk\Exceptions\NoExpoMessageException;
use Subit\ExpoSdk\Exceptions\NoExpoMessageReceiptException;
use Subit\ExpoSdk\Exceptions\PushNotificationIdLimitExceededException;
use Subit\ExpoSdk\Exceptions\PushNotificationLimitExceededException;
use Subit\ExpoSdk\Exceptions\WrongReceiptIdException;
use Subit\ExpoSdk\Expo;
use Subit\ExpoSdk\ExpoMessage;
use Subit\ExpoSdk\ExpoMessageTicket;

class ExpoTest extends TestCase
{

    public function testSendPushNotification()
    {
        $client = m::mock(Client::class);
        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $expo = new Expo($client);

        $expoMessage = ExpoMessage::create()
            ->to('ExponentPushToken[zOqdVVH-Oj278YZmOgyAhd]')
            ->title('Title')
            ->subtitle('Subtitle')
            ->body('Body text')
            ->disableSound()
            ->enableSound()
            ->badge(1)
            ->ttl(1)
            ->expiration(1)
            ->priority('high')
            ->channelId('ID')
            ->jsonData('{}');

        $expectedTicket = ExpoMessageTicket::create()
            ->id('bcc7df52-6148-40dd-8647-9a888755c2eb')
            ->status('ok');

        $responseBody = '{"data":[{"id":"ec91c359-6b4c-4578-b0ca-90e4d0612ca9","status":"ok"}]}';

        $client
            ->shouldReceive('request')
            ->once()
            ->with('POST', 'push/send', m::on(function ($argument) use ($expoMessage) {
                $headers = [];

                foreach($argument['headers'] as $header => $value)
                {
                    $headers[$header] = $value;
                }

                $jsonExpoMessage =  json_decode($argument['body'], JSON_OBJECT_AS_ARRAY)[0];


                return $expoMessage->toArray() === $jsonExpoMessage && array_key_exists('Content-Type', $headers);
            }))
            ->andReturn($response);

        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);

        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn($responseBody);

        $actualTicket = $expo->sendPushNotification($expoMessage);

        $this->assertSame($expoMessage->getTo(), $actualTicket->getToken());

        $expectedTicketArray = $expectedTicket->toArray();
        $actualTicketArray = $actualTicket->toArray();

        $expoTicketStatus = $expectedTicketArray['status'];
        $actualStatus = $actualTicketArray['status'];

        $this->assertEquals('bcc7df52-6148-40dd-8647-9a888755c2eb', $expectedTicketArray['id']);
        $this->assertEquals($expoTicketStatus, $actualStatus);
    }

    public function testSendPushNotificationMissingToken()
    {
        $expo = new Expo();

        $expoMessage = ExpoMessage::create();

        $this->expectException(MissingExponentTokenException::class);
        $expo->sendPushNotification($expoMessage);

    }

    public function testSendPushNotificationInvalidToken()
    {
        $client = m::mock(Client::class);
        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $expo = new Expo($client);

        $expoMessage = ExpoMessage::create()
            ->to('NotExponentToken');

        $responseBody = '{"data":[{"status":"error","message":"\"NotExponentToken\" is not a registered push notification recipient","details":{"error":"DeviceNotRegistered"}}]}';

        $client
            ->shouldReceive('request')
            ->once()
            ->with('POST', 'push/send', m::on(function ($argument) use ($expoMessage) {
                $headers = [];

                foreach($argument['headers'] as $header => $value)
                {
                    $headers[$header] = $value;
                }

                $jsonExpoMessage =  json_decode($argument['body'], JSON_OBJECT_AS_ARRAY)[0];


                return $expoMessage->toArray() === $jsonExpoMessage && array_key_exists('Content-Type', $headers);
            }))
            ->andReturn($response);

        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);

        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn($responseBody);

        $result = $expo->sendPushNotification($expoMessage)->toArray();

        $this->assertEquals('error', $result['status']);
        $this->assertArrayNotHasKey('id', $result);
        $this->assertArrayHasKey('message', $result);
        $this->assertArrayHasKey('details', $result);
    }

    public function testSendPushNotificationsOneInvalidToken()
    {
        $client = m::mock(Client::class);
        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $expo = new Expo($client);

        $validExpoMessage = ExpoMessage::create()
            ->to('ExponentPushToken[zOqdVVH-Oj278YZmOgyAhg]');

        $invalidExpoMessage = ExpoMessage::create()
            ->to('NotExponentToken');

        $responseBody = '{"data":[{"id":"a7bd66fd-29ff-48f8-89e0-3f660f9a7422","status":"ok"},{"status":"error","message":"\"NotExponentToken\" is not a registered push notification recipient","details":{"error":"DeviceNotRegistered"}}]}';

        $client
            ->shouldReceive('request')
            ->once()
            ->with('POST', 'push/send', m::on(function ($argument) use ($validExpoMessage, $invalidExpoMessage) {
                $headers = [];

                foreach($argument['headers'] as $header => $value)
                {
                    $headers[$header] = $value;
                }

                $jsonValidExpoMessage =  json_decode($argument['body'], JSON_OBJECT_AS_ARRAY)[0];
                $jsonInvalidExpoMessage =  json_decode($argument['body'], JSON_OBJECT_AS_ARRAY)[1];

                return $validExpoMessage->toArray() === $jsonValidExpoMessage && $invalidExpoMessage->toArray() === $jsonInvalidExpoMessage && array_key_exists('Content-Type', $headers);
            }))
            ->andReturn($response);

        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);

        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn($responseBody);

        $result = $expo->sendPushNotifications([$validExpoMessage, $invalidExpoMessage]);
        $this->assertCount(2, $result);

        $validTicket = $result[0];
        $invalidTicket = $result[1];

        $this->assertSame($validExpoMessage->getTo(), $validTicket->getToken());
        $this->assertSame($invalidExpoMessage->getTo(), $invalidTicket->getToken());

        $validResponse = $validTicket->toArray();
        $invalidResponse = $invalidTicket->toArray();

        $this->assertArrayHasKey('id', $validResponse);
        $this->assertEquals('ok', $validResponse['status']);
        $this->assertArrayNotHasKey('message', $validResponse);
        $this->assertArrayNotHasKey('details', $validResponse);

        $this->assertArrayNotHasKey('id', $invalidResponse);
        $this->assertEquals('error', $invalidResponse['status']);
        $this->assertArrayHasKey('message', $invalidResponse);
        $this->assertArrayHasKey('details', $invalidResponse);

    }

    public function testGetPushNotificationReceipts()
    {
        $client = m::mock(Client::class);
        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $expo = new Expo($client);

        $tickets = [$ticketA = '1baa8861-15e6-4202-a1b4-31119319e1bf', $ticketB = 'c25ed154-b214-46bc-8212-9fec4c53220f'];

        $client
            ->shouldReceive('request')
            ->once()
            ->with('POST', 'push/getReceipts', m::on(function ($argument) use ($tickets) {
                $headers = [];
                $tickets = ['ids' => $tickets];

                foreach($argument['headers'] as $header => $value)
                {
                    $headers[$header] = $value;
                }

                $jsonTickets =  json_decode($argument['body'], JSON_OBJECT_AS_ARRAY);

                return $tickets === $jsonTickets && array_key_exists('Content-Type', $headers);
            }))
            ->andReturn($response);

        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);

        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('{"data":{"1baa8861-15e6-4202-a1b4-31119319e1bf":{"status":"ok"},"c25ed154-b214-46bc-8212-9fec4c53220f":{"status":"ok"}}}');


        $returnedReceipts = $expo->getPushNotificationReceipts($tickets);
        $this->assertCount(2, $returnedReceipts);

        $this->assertArrayHasKey($ticketA, $returnedReceipts);
        $this->assertArrayHasKey($ticketB, $returnedReceipts);

        $receiptA = $returnedReceipts[$ticketA];
        $receiptB = $returnedReceipts[$ticketB];

        $expectedStatus = 'ok';
        $this->assertSame($expectedStatus, $receiptA->getStatus());
        $this->assertSame($expectedStatus, $receiptB->getStatus());
    }

    public function testGetPushNotificationReceipt()
    {
        $client = m::mock(Client::class);
        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $expo = new Expo($client);

        $ticketId = '1baa8861-15e6-4202-a1b4-31119319e1bf';

        $client
            ->shouldReceive('request')
            ->once()
            ->with('POST', 'push/getReceipts', m::on(function ($argument) use ($ticketId) {
                $headers = [];
                $ticket = ['ids' => [$ticketId]];

                foreach($argument['headers'] as $header => $value)
                {
                    $headers[$header] = $value;
                }

                $jsonTicket =  json_decode($argument['body'], JSON_OBJECT_AS_ARRAY);

                return $ticket === $jsonTicket && array_key_exists('Content-Type', $headers);
            }))
            ->andReturn($response);

        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);

        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('{"data":{"1baa8861-15e6-4202-a1b4-31119319e1bf":{"status":"ok"},"c25ed154-b214-46bc-8212-9fec4c53220f":{"status":"ok"}}}');

        $receipt = $expo->getPushNotificationReceipt($ticketId);

        $actualId = $receipt->toArray()['id'];

        $expectedStatus = 'ok';
        $actualStatus = $receipt->toArray()['status'];

        $this->assertEquals($ticketId, $actualId);
        $this->assertEquals($expectedStatus, $actualStatus);

    }

    public function testGetPushNotificationWithInvalidReceiptId()
    {
        $client = m::mock(Client::class);
        $response = m::mock(ResponseInterface::class);
        $stream = m::mock(StreamInterface::class);

        $expo = new Expo($client);

        $invalidTicketId = 'NotATicketId';

        $client
            ->shouldReceive('request')
            ->once()
            ->with('POST', 'push/getReceipts', m::on(function ($argument) use ($invalidTicketId) {
                $headers = [];
                $ticket = ['ids' => [$invalidTicketId]];

                foreach($argument['headers'] as $header => $value)
                {
                    $headers[$header] = $value;
                }

                $jsonTicket =  json_decode($argument['body'], JSON_OBJECT_AS_ARRAY);

                return $ticket === $jsonTicket && array_key_exists('Content-Type', $headers);
            }))
            ->andReturn($response);

        $response
            ->shouldReceive('getBody')
            ->once()
            ->andReturn($stream);

        $stream
            ->shouldReceive('__toString')
            ->once()
            ->andReturn('{"data":{}}');


        $this->expectException(WrongReceiptIdException::class);

        $expo->getPushNotificationReceipt($invalidTicketId);

    }

    public function testSendPushNotificationsEmptyArray()
    {
        $client = m::mock(Client::class);

        $expo = new Expo($client);

        $this->expectException(NoExpoMessageException::class);

        $expo->sendPushNotifications([]);
    }

    public function testSendPushNotificationsLimitExceeded()
    {
        $expo = new Expo();

        $expoMessages = [];
        for ($i = 0; $i < 101; $i++) {
            $expoMessage = ExpoMessage::create()
                ->to('ExponentPushToken[zOqdVVH-Oj278YZmOgyAfq]');

            array_push($expoMessages, $expoMessage);
        }
        $this->expectException(PushNotificationLimitExceededException::class);
        $expo->sendPushNotifications($expoMessages);

    }

    public function testGetPushNotificationIdLimitExceeded()
    {
        $expo = new Expo();

        $ids = range(1, 301);

        $this->expectException(PushNotificationIdLimitExceededException::class);
        $expo->getPushNotificationReceipts($ids);

    }

    public function testGetPushNotificationEmptyArray()
    {
        $expo = new Expo();

        $this->expectException(NoExpoMessageReceiptException::class);
        $receipts = $expo->getPushNotificationReceipts([]);

        $this->assertEquals([], $receipts);
    }


    public function testChunksNotificationsIntoCorrectBatchSizes()
    {
        $expo = new Expo();

        $messages = range(1, 1001);

        $chunks = $expo->chunkPushNotifications($messages);
        $this->assertEquals(11, count($chunks));
        $this->assertEquals(100, count($chunks[0]));
        $this->assertEquals(1, count($chunks[10]));

    }

    public function testChunksNotificationReceiptsIntoCorrectBatchSizes()
    {
        $expo = new Expo();

        $ids = range(1, 901);

        $chunks = $expo->chunkPushNotificationReceiptIds($ids);
        $this->assertEquals(4, count($chunks));
        $this->assertEquals(300, count($chunks[0]));
        $this->assertEquals(1, count($chunks[3]));

    }

    public function testIsExpoPushTokenWithValidToken()
    {
        $expo = new Expo();

        $token1 = 'ExponentPushToken[xxxxxx-xxxxxxxxx]';
        $token2 = 'ExpoPushToken[00000-000000000]';

        $this->assertTrue($expo->isValidTokenFormat($token1));
        $this->assertTrue($expo->isValidTokenFormat($token2));
    }

    public function testIsExpoPushTokenWithInvalidToken()
    {
        $expo = new Expo();

        $token1 = 'ExponentPush[xxxxxx-xxxxxxxxx]';
        $token2 = 'ExpoPushToken[00000-000000000';
        $token3 = 'ExponentPushTokenxxxxxx-xxxxxxxxx]';

        $this->assertFalse($expo->isValidTokenFormat($token1));
        $this->assertFalse($expo->isValidTokenFormat($token2));
        $this->assertFalse($expo->isValidTokenFormat($token3));
    }

    public function testIsExpoReceiptIdWithValidId()
    {
        $expo = new Expo();

        $id1 = '40102624-ff32-4438-8ecd-9ffee8a68fbw';
        $id2 = '5afe1058-93a3-4d75-b874-a2d61d029500';

        $this->assertTrue($expo->isValidReceiptIdFormat($id1));
        $this->assertTrue($expo->isValidReceiptIdFormat($id2));
    }

    public function testIsExpoReceiptIdWithInvalidId()
    {
        $expo = new Expo();

        $id1 = '4012624-ff32-4438-8ecd-9ffee8a68fbw';
        $id2 = '5afe1058-93a34d75-b874-a2d61d029500';

        $this->assertFalse($expo->isValidReceiptIdFormat($id1));
        $this->assertFalse($expo->isValidReceiptIdFormat($id2));
    }
}
