<?php

namespace App\Tests\unit\Service\Config;

use App\Service\Config\AppConfig;
use PHPUnit\Framework\TestCase;

class AppConfigTest extends TestCase
{
    /** @test */
    public function getBaseUrl_returns_correct_data()
    {
        $baseUrl = 'https://example.com/';
        $config = new AppConfig($baseUrl);

        $this->assertSame($baseUrl, $config->getBaseUrl());
    }
}