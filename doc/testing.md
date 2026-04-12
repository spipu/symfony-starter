# Testing

## Test suites

Two suites are defined in `website/.phpunit.xml` :

| Suite | Folder | Base class |
|---|---|---|
| Unit Tests | `website/tests/Unit/` | `PHPUnit\Framework\TestCase` |
| Functional Tests | `website/tests/Functional/` | `App\Tests\WebTestCase` |

## Prerequisites

Install the following packages on your host machine:

```bash
sudo apt-get install php8.3-cli php8.3-curl php-xdebug php-pdo php-sqlite3 firefox
```

The WebDriver binaries (`geckodriver`, `chromedriver`) are already bundled in `website/drivers/` — no separate install needed.

## Run the tests

Commands must be run from the **repository root** (not from `website/`):

```bash
# Run all tests (unit + functional)
./quality/phpunit.sh

# Run with code coverage (opens Firefox report)
./quality/phpunit.sh --coverage
```

The script:
- Generates a temporary encryption key pair (`APP_ENCRYPTOR_KEY_PAIR`)
- Raises the open file limit (`ulimit -n 8192`)
- Uses a dedicated SQLite database (`website/var-test/test.sqlite`)
- Cleans up the Symfony cache after the run

## Unit tests

Extend `PHPUnit\Framework\TestCase` directly. No database, no HTTP.

```php
class MyServiceTest extends TestCase
{
    public function testSomething(): void
    {
        $service = new MyService();
        $this->assertSame('expected', $service->doSomething());
    }
}
```

## Functional tests (Panther)

Functional tests use [Symfony Panther](https://github.com/symfony/panther) to drive a real Firefox browser against the running dev environment (`https://starter.lxd`).

Extend `App\Tests\WebTestCase` and add `#[AllowMockObjectsWithoutExpectations]` on every concrete test class:

```php
#[AllowMockObjectsWithoutExpectations]
class MainTest extends WebTestCase
{
    public function testHomePage(): void
    {
        $client = static::createPantherClient();

        $client->request('GET', '/');
        $this->assertClientIs200($client);
    }
}
```

### How WebTestCase works

- Connects to `https://starter.lxd` via `external_base_uri` (no internal Panther server)
- Accepts self-signed SSL certificates (`acceptInsecureCerts`)
- Window size: 1920×1080
- Fixtures are loaded during `setUp()` via `prepareGlobalDataPrimer()`

### Test credentials

| Field | Value |
|---|---|
| Username | `spipu` |
| Password | `password` |

### Available helpers

| Method | Description |
|---|---|
| `assertLogIn($client, $user, $pass)` | Full login flow with assertions |
| `logIn($client, $user, $pass)` | Submit the login form |
| `logOut($client)` | Navigate to `/logout` |
| `assertClientIs200($client)` | Assert no exception page, take screenshot on failure |
| `assertClientHasAlert($client, $message)` | Assert a `[role=alert]` contains the message |
| `assertClientHasErrorAlert($client, $message)` | Assert a `.alert-danger` |
| `assertClientHasSuccessAlert($client, $message)` | Assert a `.alert-success` |
| `assertSamePageTitle($expected, $client)` | Assert `<h1>` text (case-insensitive) |
| `assertFieldValue($crawler, $field, $value)` | Assert a Spipu UI detail field |
| `assertDataGridFieldValue($crawler, $field, $value)` | Assert a Spipu UI grid cell |
| `hasLink($client, $text)` | Assert a link is visible |
| `hasNotLink($client, $text)` | Assert a link is absent |
| `takeScreenshot()` | Save a PNG to `website/var-test/screenshot/` |
| `navigateLogin($client)` | Log in and take a screenshot |
| `navigateLink($client, $menu, $subMenu)` | Click menu + submenu and assert 200 |
| `resetEmailsPool()` | Clear the captured email queue |

### Screenshots

Screenshots are saved automatically on assertion failure and can be triggered manually via `takeScreenshot()`:

| Path | Content |
|---|---|
| `website/var-test/screenshot/` | Manual screenshots |
| `website/var-test/error-screenshots/` | Panther error screenshots |

## Test Kernel

`website/tests/Kernel.php` extends `SpipuKernel` and overrides two parameters for the test context:

| Parameter | Test value | Reason |
|---|---|---|
| `APP_SETTINGS_APP_CODE` | `dev` | Load dev fixtures and config |
| `APP_SETTINGS_MAILER_DSN` | `null://default` | Disable real email sending |

## Folder structure

```
website/
├── drivers/
│   ├── geckodriver      # Firefox WebDriver (used by Panther)
│   └── chromedriver     # Chrome WebDriver (alternative)
├── tests/
│   ├── bootstrap.php    # PHPUnit bootstrap
│   ├── Kernel.php       # Test Kernel
│   ├── WebTestCase.php  # Base class for functional tests
│   ├── Functional/
│   │   ├── MainTest.php
│   │   └── ...
│   └── Unit/
│       └── Service/
│           └── ...
└── var-test/
    ├── test.sqlite              # SQLite test database
    ├── screenshot/              # Manual screenshots
    └── error-screenshots/       # Panther error screenshots
```
