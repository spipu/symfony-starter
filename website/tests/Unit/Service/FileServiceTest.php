<?php
namespace App\Tests\Unit\Service;

use App\Service\FileService;
use PHPUnit\Framework\TestCase;

class FileServiceTest extends TestCase
{
    static public function getService(TestCase $testCase): FileService
    {
        return new FileService();
    }

    public function testService(): void
    {
        $service = self::getService($this);
        $this->assertSame('0.0 Ko', $service->getHumanFileSize(0));

        $this->assertSame('4.2 Ko', $service->getHumanFileSize((int) (4.2 * 1024)));
        $this->assertSame('4.2 Mo', $service->getHumanFileSize((int) (4.2 * 1024 * 1024)));
        $this->assertSame('4.2 Go', $service->getHumanFileSize((int) (4.2 * 1024 * 1024 * 1024)));
    }
}
