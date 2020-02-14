<?php

namespace Subit\ExpoSdk\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use GuzzleHttp\Client;

abstract class TestCase extends Orchestra
{

    public function setUp(): void
    {
        parent::setUp();

    }

    protected function getPackageProviders($app)
    {
        return [];
    }
}
