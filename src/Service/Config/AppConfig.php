<?php

namespace App\Service\Config;

class AppConfig
{
    public function __construct(
        private string $baseUrl
    )
    {
    }

    public function getBaseUrl(): string
    {
        return $this->baseUrl;
    }

}