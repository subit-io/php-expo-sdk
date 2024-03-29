<?php

namespace Subit\ExpoSdk;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Subit\ExpoSdk\Exceptions\ApiTransferException;
use Subit\ExpoSdk\Exceptions\NoExpoMessageException;
use Subit\ExpoSdk\Exceptions\WrongReceiptIdException;
use Subit\ExpoSdk\Exceptions\ExpoApiEndpointException;
use Subit\ExpoSdk\Exceptions\NoExpoMessageReceiptException;
use Subit\ExpoSdk\Exceptions\MissingExponentTokenException;
use Subit\ExpoSdk\Exceptions\PushNotificationLimitExceededException;
use Subit\ExpoSdk\Exceptions\PushNotificationIdLimitExceededException;

class Expo
{
    const PUSH_NOTIFICATIONS_CHUNK_LIMIT = 100;
    const PUSH_NOTIFICATION_RECEIPT_CHUNK_LIMIT = 300;

    protected $client;
    protected $requestHeaders = ['Content-Type' => 'application/json'];

    public function __construct(Client $client = null)
    {
        if (isset($client)) {
            $this->client = $client;
        } else {
            $this->client = new Client(['base_uri' => 'https://exp.host/--/api/v2/']);
        }
    }

    public function sendPushNotification(ExpoMessage $message): ExpoMessageTicket
    {
        return $this->sendPushNotifications([$message])[0];
    }

    public function sendPushNotifications(array $expoMessages): array
    {
        $expoMessageCount = count($expoMessages);

        if ($expoMessageCount === 0) {
            throw new NoExpoMessageException();
        }

        if ($expoMessageCount > self::PUSH_NOTIFICATIONS_CHUNK_LIMIT) {
            throw new PushNotificationLimitExceededException(
                self::PUSH_NOTIFICATIONS_CHUNK_LIMIT,
                $expoMessageCount);
        }

        $requestBody = [];
        $tickets = [];

        /* @var ExpoMessage $expoMessage */
        foreach ($expoMessages as $expoMessage) {
            $arrayExpoMessage = $expoMessage->toArray();
            if (!array_key_exists('to', $arrayExpoMessage)) {
                throw new MissingExponentTokenException();
            }
            $requestBody[] = $arrayExpoMessage;
            $tickets[] = (new ExpoMessageTicket)->token($expoMessage->getTo());
        }

        $requestBody = json_encode($requestBody);

        try {
            $response = $this->client->request(
                'POST',
                'push/send',
                ['headers' => $this->requestHeaders, 'body' => $requestBody]);
        } catch (GuzzleException $e) {
            throw new ApiTransferException($e->getMessage(), $e->getCode(), $e);
        }

        $responseBody = json_decode($response->getBody()->__toString());

        if (property_exists($responseBody, 'errors')) {
            throw new ExpoApiEndpointException($responseBody->errors);
        }

        return $this->buildTickets($responseBody->data, $tickets);
    }

    public function getPushNotificationReceipt($ticketId): ExpoMessageReceipt
    {
        $receiptInArray = $this->getPushNotificationReceipts([$ticketId]);

        if (empty($receiptInArray)) {
            throw new WrongReceiptIdException();
        }

        return current($receiptInArray);
    }

    public function getPushNotificationReceipts(array $ticketIds)
    {
        if (empty($ticketIds)) {
            throw new NoExpoMessageReceiptException();
        }

        if (count($ticketIds) > self::PUSH_NOTIFICATION_RECEIPT_CHUNK_LIMIT) {

            throw new PushNotificationIdLimitExceededException(
                self::PUSH_NOTIFICATIONS_CHUNK_LIMIT,
                count($ticketIds));
        }

        $ticketIds = ['ids' => $ticketIds];
        $requestBody = json_encode($ticketIds);

        $response = $this->client->request(
            'POST',
            'push/getReceipts',
            ['headers' => $this->requestHeaders, 'body' => $requestBody]);

        $responseBody = json_decode($response->getBody()->__toString());

        if (property_exists($responseBody, 'errors')) {
            throw new ExpoApiEndpointException($responseBody->errors);
        }

        return $this->buildReceipts($responseBody->data);
    }

    public function isValidTokenFormat(string $token): bool
    {
        return preg_match('/^(ExponentPushToken|ExpoPushToken)\[.*\]$/', $token);
    }

    public function isValidReceiptIdFormat(string $id): bool
    {
        return preg_match('/^[a-z\d]{8}-[a-z\d]{4}-[a-z\d]{4}-[a-z\d]{4}-[a-z\d]{12}$/', $id);
    }

    private function buildTickets($rawTickets, $tickets)
    {
        $counter = 0;

        foreach ($rawTickets as $rawTicket) {
            $ticket = $tickets[$counter];

            if (property_exists($rawTicket, 'id')) {
                $ticket->id($rawTicket->id);
            }

            $ticket
                ->status($rawTicket->status);

            if (property_exists($rawTicket, 'message')) {
                $ticket->message($rawTicket->message);
            }

            if (property_exists($rawTicket, 'details')) {
                $ticket->details(json_encode($rawTicket->details));
            }
            $counter++;
        }
        return $tickets;
    }

    private function buildReceipts($rawReceipts): array
    {
        $receipts = [];

        foreach ($rawReceipts as $id => $rawReceipt) {

            $receipt = new ExpoMessageReceipt();

            $receipt
                ->id($id)
                ->status($rawReceipt->status);

            if (property_exists($rawReceipt, 'message')) {
                $receipt->message($rawReceipt->message);
            }

            if (property_exists($rawReceipt, 'details')) {
                $receipt->details($rawReceipt->details);
            }

            $receipts[$id] = $receipt;
        }

        return $receipts;
    }

    public function chunkPushNotifications($expoMessages): array
    {
        return $this->chunk($expoMessages, self::PUSH_NOTIFICATIONS_CHUNK_LIMIT);
    }

    public function chunkPushNotificationReceiptIds($receiptIds): array
    {
        return $this->chunk($receiptIds, self::PUSH_NOTIFICATION_RECEIPT_CHUNK_LIMIT);
    }

    private function chunk($chunkable, $batchSize)
    {
        $chunks = [];
        $pointer = 0;
        $chunkableSize = count($chunkable);

        do {

            if ($pointer + $batchSize <= $chunkableSize) {

                $slice = array_slice($chunkable, $pointer, $batchSize);
                $chunks[] = $slice;
                $pointer += $batchSize;

            } else {

                $slice = array_slice($chunkable, $pointer, $chunkableSize);
                $chunks[] = $slice;
                break;
            }

        } while ($pointer < $chunkableSize);

        return $chunks;
    }
}
