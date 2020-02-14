<?php


namespace Subit\ExpoSdk\Exceptions;

use Exception;

class PushNotificationIdLimitExceededException extends Exception
{
    public function __construct($limit, $count)
    {
        parent::__construct();

        $this->message = sprintf(
            'Batch limit is %d. Received %d. chunkPushNotificationsIds() can be used to chunk the data.'
            , $limit, $count);
    }
}