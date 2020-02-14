<?php


namespace Subit\ExpoSdk\Tests;

use Subit\ExpoSdk\Exceptions\InvalidNotificationDataException;
use Subit\ExpoSdk\Exceptions\NotAValidPriorityException;
use Subit\ExpoSdk\ExpoMessage;
use Subit\ExpoSdk\Priority;

class ExpoMessageTest extends TestCase
{
    public function testExpoMessageValidPriority()
    {
        $valids = [Priority::HIGH_PRIORITY, Priority::NORMAL_PRIORITY, Priority::DEFAULT_PRIORITY];

        foreach($valids as $valid){
            $message =  ExpoMessage::create()
                ->priority($valid);
            $this->assertEquals($valid, $message->getPriority());
        }
    }

    public function testExpoMessageInvalidPriority()
    {
        $this->expectException(InvalidNotificationDataException::class);
        $expoMessage = ExpoMessage::create()
            ->priority('NonValidPriority');

    }

    public function testExpoMessageValidJson()
    {
        $this->expectException(InvalidNotificationDataException::class);
        $expoMessage = ExpoMessage::create()
            ->jsonData('{');

    }

    public function testExpoMessageInvalidJson()
    {
        $json = '{"test" : "Hello World"}';
        $expoMessage = ExpoMessage::create()
            ->jsonData($json);

        $this->assertEquals($json, $expoMessage->getJsonData());

    }
}