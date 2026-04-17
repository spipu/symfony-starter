<?php

declare(strict_types=1);

namespace Laurent\CoreBundle\Service;

class HostService
{
    private string $host;

    public function __construct(
        string $host,
    ) {
        $this->host = $host;
    }

    public function getHost(): string
    {
        return $this->host;
    }
}
