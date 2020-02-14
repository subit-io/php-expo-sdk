<?php


namespace Subit\ExpoSdk\Exceptions;

use Exception;
use Throwable;

class PushNotificationLimitExceededException extends Exception
{
    public function __construct($limit, $count)
{
    parent::__construct();

    $this->message = sprintf(
        'Batch limit is %d. Received %d. chunkPushNotifications() can be used to chunk the data.'
        , $limit, $count);
}
}