<?php

declare(strict_types=1);

namespace App\Tests;

use Exception;
use Facebook\WebDriver\WebDriverDimension;
use Spipu\CoreBundle\Tests\WebTestCaseTrait;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;

global $nbScreenShot;
$nbScreenShot = 0;

class WebTestCase extends PantherTestCase
{
    use WebTestCaseTrait;

    public const ADMIN_USER = 'spipu';
    public const ADMIN_PASS = 'password';

    protected array $testModes = [
        'back-office' => '127.0.0.1',
    ];

    protected string $testMode;
    protected static ?PantherClient $clientCache = null;

    public function setUp(): void
    {
        if (!in_array($this->testMode, array_keys($this->testModes))) {
            throw new Exception('Invalid Test Mode');
        }

        $hostName = $this->testModes[$this->testMode];

        $options = [
            'browser'  => self::FIREFOX,
            'hostname' => $hostName,
        ];

        $kernelOptions = [];

        $managerOptions = [
            'capabilities'             => ['acceptInsecureCerts' => true],
            'connection_timeout_in_ms' => 10000,
            'request_timeout_in_ms'    => 10000,
        ];

        self::$clientCache = parent::createPantherClient($options, $kernelOptions, $managerOptions);
        $size = new WebDriverDimension(1920, 1080);
        self::$clientCache->manage()->window()->setSize($size);

        $this->prepareDataPrimer(self::$kernel, static::getContainer());
    }

    public function tearDown(): void
    {
        if (self::$clientCache !== null) {
            self::$clientCache->quit();
            self::$clientCache = null;
        }
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    /**
     * @SuppressWarnings(PMD.UnusedFormalParameter)
     */
    protected static function createPantherClient(
        array $options = [],
        array $kernelOptions = [],
        array $managerOptions = []
    ): PantherClient {
        self::$clientCache->getCookieJar()->clear();
        return self::$clientCache;
    }

    protected function assertLogIn(PantherClient $client, string $username, string $password): void
    {
        // Home page => redirects to login
        $client->request('GET', '/');

        // On login page?
        $this->assertClientIs200($client);
        $this->assertGreaterThan(0, $client->getCrawler()->selectButton('Log In')->count());

        $this->logIn($client, $username, $password);

        // Good credentials => home page with Log Out link
        $this->assertClientIs200($client);
        $this->assertGreaterThan(0, $client->getCrawler()->selectLink('Log Out')->count());
        $this->assertSame(0, $client->getCrawler()->selectLink('Log In')->count());
    }

    protected function logIn(
        PantherClient $client,
        string $username,
        string $password,
        string $buttonSelector = 'Log In'
    ): void {
        $client->submit(
            $client->getCrawler()->selectButton($buttonSelector)->form(),
            [
                '_username' => $username,
                '_password' => $password,
            ]
        );
    }

    protected function logOut(PantherClient $client): void
    {
        $client->request('GET', '/logout');
    }

    protected function assertClientIs200(PantherClient $client): void
    {
        $exception = $client->getCrawler()->filter('h1.exception-message');

        $message = '200 - OK';
        if ($exception->count()) {
            $message = $exception->text();
            $this->takeScreenshot();
        }

        $this->assertSame('200 - OK', $message);
    }

    public function takeScreenshot(string $suffix = ''): void
    {
        global $nbScreenShot;
        $nbScreenShot++;

        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2);

        $classNameParts = explode(
            '/',
            str_replace(
                '\\',
                '/',
                trim(
                    str_replace('App\\Tests\\Functional\\', '', $traces[1]['class']),
                    '\\'
                )
            ),
            2
        );

        $folder = $classNameParts[0];
        $parts = [
            str_pad((string) $nbScreenShot, 4, '0', STR_PAD_LEFT),
            str_replace('/', '_', $classNameParts[1]),
            $traces[1]['function'],
            $traces[0]['line'],
        ];

        if ($suffix !== '') {
            $parts[] = str_replace(' ', '_', mb_strtolower($suffix));
        }

        $path = './var-test/screenshot/' . $folder . '/' . implode('-', $parts) . '.png';
        self::$clientCache->takeScreenshot($path);
    }

    protected function assertClientHasAlert(
        PantherClient $client,
        string $message,
        string $filter = 'div[role=alert]'
    ): void {
        $alert = $client->getCrawler()->filter($filter);

        $this->assertGreaterThan(0, $alert->count());
        $this->assertStringContainsString($message, $alert->text());
    }

    protected function assertClientHasErrorAlert(PantherClient $client, string $message): void
    {
        $this->assertClientHasAlert($client, $message, 'div.alert-danger[role=alert]');
    }

    protected function assertSamePageTitle(
        string $expected,
        PantherClient $client,
        string $selector = 'h1'
    ): void {
        $actual = $client->getCrawler()->filter($selector)->text(null, true);
        $this->assertSame(mb_strtolower($expected), mb_strtolower($actual));
    }
}
