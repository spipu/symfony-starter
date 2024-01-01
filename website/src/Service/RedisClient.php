<?php

declare(strict_types=1);

namespace App\Service;

use Predis\Client;

class RedisClient
{
    private string $redisDsn;
    private ?Client $client = null;

    public function __construct(string $redisDsn)
    {
        $this->redisDsn = $redisDsn;
    }

    private function create(): void
    {
        $this->client = new Client($this->redisDsn);
    }

    public function get(): Client
    {
        if ($this->client === null) {
            $this->create();
        }

        return $this->client;
    }
}
