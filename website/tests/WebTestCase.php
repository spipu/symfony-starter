<?php
namespace App\Tests;

use Exception;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\Panther\Client;
use Symfony\Component\Panther\Client as PantherClient;
use Symfony\Component\Panther\PantherTestCase;
use Facebook\WebDriver\WebDriverDimension;

global $nbScreenShot;
$nbScreenShot = 0;

class WebTestCase extends PantherTestCase
{
    use WebTestCaseTrait;

    public const ADMIN_USER = 'spipu';
    public const ADMIN_PASS = 'password';

    protected string $testHost = 'starter.lxd';

    protected string $testMode;
    protected bool $hideRealBadCredentialError = true;
    protected static ?PantherClient $clientCache = null;

    public function setUp(): void
    {
        $hostName = $this->testHost;

        $options = [
            'browser'  => self::FIREFOX,
            'hostname' => $hostName,
        ];

        $kernelOptions = [];

        self::$clientCache = parent::createPantherClient($options, $kernelOptions);
        $size = new WebDriverDimension(1920,1080);
        self::$clientCache->manage()->window()->setSize($size);

         $this->prepareGlobalDataPrimer(self::$kernel, static::getContainer());
    }

    /**
     * @return void
     */
    public function tearDown(): void
    {
        self::stopWebServer();
    }

    protected static function getKernelClass(): string
    {
        return Kernel::class;
    }

    protected static function createPantherClient(array $options = [], array $kernelOptions = [], array $managerOptions = []): PantherClient
    {
        self::$clientCache->getCookieJar()->clear();
        return self::$clientCache;
    }

    protected function assertLogIn(PantherClient $client, string $username, string $password): void
    {
        // Home page
        $client->request('GET', '/');
        $this->assertClientIs200($client);

        // Go to the login page
        $client->clickLink('Log In');
        $this->assertClientIs200($client);
        $this->assertGreaterThan(0, $client->getCrawler()->selectButton('Log In')->count());

        $this->logIn($client, $username, $password);

        // Good Credentials
        $this->assertClientIs200($client);
        $this->assertSamePageTitle('Starter', $client);

        $this->assertStringContainsStringIgnoringCase(
            'You are on the Production environment.',
            $client->getCrawler()->text()
        );

        $this->assertGreaterThan(0, $client->getCrawler()->selectLink('Log Out')->count());
        $this->assertEquals(0, $client->getCrawler()->selectLink('Log In')->count());
    }

    protected function logIn(PantherClient $client, string $username, string $password, string $buttonSelector = 'Log In'): void
    {
        // Login Submit
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

        $this->assertEquals('200 - OK', $message);
    }

    public function takeScreenshot(int $level = 0, string $suffix = ''): void
    {
        global $nbScreenShot;
        $nbScreenShot++;

        $traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, $level + 2);

        $className = str_replace(
            '\\',
            '/',
            trim(
                str_replace('App\\Tests\\Functional\\', '', $traces[$level + 1]['class']),
                '\\'
            )
        );

        $parts = [
            str_pad((string) $nbScreenShot, 4, '0', STR_PAD_LEFT),
            str_replace('/', '_', $className),
            $traces[$level + 1]['function'],
            $traces[$level]['line']
        ];

        if ($suffix !== '') {
            $parts[] = str_replace(' ', '_', mb_strtolower($suffix));
        }

        $path = './var-test/screenshot/' . implode('-', $parts) . '.png';
        self::$clientCache->takeScreenshot($path);
    }

    public function resetEmailsPool(): void
    {
        static::getContainer()->get('mailer.logger_message_listener')->reset();
    }

    public function hasNotLink(PantherClient $client, string $linkText): void
    {
        $links = $client->getCrawler()->selectLink($linkText);

        $this->assertSame(0, $links->count());
    }

    public function hasLink(PantherClient $client, string $linkText): void
    {
        $links = $client->getCrawler()->selectLink($linkText);

        $this->assertGreaterThan(0, $links->count());
    }

    protected function assertClientHasAlert(PantherClient $client, string $message, string $filter = 'div[role=alert]'): void
    {
        $alert = $client->getCrawler()->filter($filter);

        $this->assertGreaterThan(0, $alert->count());
        $this->assertStringContainsString($message, $alert->text());
    }

    protected function assertClientHasErrorAlert(PantherClient $client, string $message): void
    {
        $this->assertClientHasAlert($client, $message, 'div.alert-danger[role=alert]');
    }

    protected function assertClientHasSuccessAlert(PantherClient $client, string $message): void
    {
        $this->assertClientHasAlert($client, $message, 'div.alert-success[role=alert]');
    }

    protected function assertFieldValue(Crawler $crawler, string $fieldName, string $fieldValue): void
    {
        $this->assertSame($fieldValue, $crawler->filter('td[data-field-name="'.$fieldName.'"]')->text(null, true));
    }

    protected function formatAmount(float $amount, string $currency): string
    {
        return number_format($amount, 2, ',', '') . ' ' . $currency;
    }

    protected function assertDataGridFieldValue(Crawler $crawler, string $fieldName, string $fieldValue): void
    {
        $this->assertSame($fieldValue,
            $crawler
                ->filter('td[data-grid-field-name="'.$fieldName.'"]')
                ->text(null, true)
        );
    }

    protected function assertSamePageTitle(string $expected, PantherClient $client, string $selector = 'h1'): void
    {
        $actual = $client->getCrawler()->filter($selector)->text(null, true);

        $this->assertSame(mb_strtolower($expected), mb_strtolower($actual));
    }

    protected function navigateLogin(Client $client): void
    {
        $this->assertLogIn($client, self::ADMIN_USER, self::ADMIN_PASS);
        $this->takeScreenshot(1, 'navigate-login');
        $this->assertClientIs200($client);
        $client->refreshCrawler();
    }

    protected function navigateLink(Client $client, string $menu, string $subMenu): void
    {
        $client->clickLink($menu);
        $client->clickLink($subMenu);
        $this->takeScreenshot(1, $menu . '-' . $subMenu);
        $this->assertClientIs200($client);
        $client->refreshCrawler();
    }

    protected function navigateLinkAndSubLinks(Client $client, string $menu, string $subMenu, array $subLinks): void
    {
        $client->clickLink($menu);
        $client->clickLink($subMenu);
        $subLinksName = '';
        foreach ($subLinks as $subLink) {
            $client->clickLink($subLink);
            $subLinksName .= '-' . $subLink;
        }
        $this->takeScreenshot(1, $menu . '-' . $subMenu . $subLinksName);
        $this->assertClientIs200($client);
        $client->refreshCrawler();
    }
}
