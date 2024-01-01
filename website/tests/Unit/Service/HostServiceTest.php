<?php
namespace App\Tests\Unit\Service;

use App\Service\HostService;
use PHPUnit\Framework\TestCase;

class HostServiceTest extends TestCase
{
    static public function getService(TestCase $testCase, string $host = 'test.lxd'): HostService
    {
        return new HostService($host);
    }

    public function testService(): void
    {
        $service = self::getService($this, 'first.lxd');
        $this->assertSame('first.lxd', $service->getHost());
    }
}
