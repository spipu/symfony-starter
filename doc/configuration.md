# Configuration

## How configuration is loaded

Configuration parameters are resolved in this order (last wins):

1. `website/config/app_default_configuration.yaml` — default values (SQLite, no Redis, null mailer)
2. `/etc/starter/symfony.yaml` — production overrides (ignored if file does not exist)

The production file `/etc/starter/symfony.yaml` is provisioned by the deployment scripts and is **not** tracked in the repository.

## Parameters reference

| Parameter | Default value | Description |
|---|---|---|
| `APP_SETTINGS_APP_ENV` | `prod` | Symfony environment (`prod`, `dev`, `test`) |
| `APP_SETTINGS_APP_CODE` | `prod` | Internal env code used by SpipuCoreBundle (`prod`, `dev`) |
| `APP_SETTINGS_APP_SECRET` | `TEMPORARY_SECRET` | Symfony secret — **must be changed in production** |
| `APP_SETTINGS_APP_ENCRYPTOR_KEY_PAIR` | _(from env var)_ | SpipuCoreBundle encryption key pair (base64 sodium keypair) |
| `APP_SETTINGS_DATABASE_URL` | `sqlite:///.../test.sqlite` | Doctrine DBAL connection URL |
| `APP_SETTINGS_DATABASE_VERSION` | `3.37.0` | Database server version (SQLite) |
| `APP_SETTINGS_MAILER_DSN` | `null://default` | Symfony Mailer DSN |
| `APP_SETTINGS_CACHE_APP` | `cache.adapter.filesystem` | Symfony cache adapter |
| `APP_SETTINGS_REDIS_CACHE_HOST` | _(empty)_ | Redis cache server hostname |
| `APP_SETTINGS_REDIS_CACHE_PORT` | _(empty)_ | Redis cache server port |
| `APP_SETTINGS_REDIS_CACHE_DB` | _(empty)_ | Redis cache database index |
| `APP_SETTINGS_REDIS_SESSION_HOST` | _(empty)_ | Redis session server hostname |
| `APP_SETTINGS_REDIS_SESSION_PORT` | _(empty)_ | Redis session server port |
| `APP_SETTINGS_REDIS_SESSION_DB` | _(empty)_ | Redis session database index |
| `APP_SETTINGS_SESSION_HANDLER` | `null` | Session handler service id (`null` = filesystem) |
| `ENV_HOST` | `starter.lxd` | Public hostname used by HostService |

## Production example (`/etc/starter/symfony.yaml`)

```yaml
parameters:
    APP_SETTINGS_APP_ENV:          'prod'
    APP_SETTINGS_APP_CODE:         'prod'
    APP_SETTINGS_APP_SECRET:       'your-strong-secret-here'
    APP_SETTINGS_APP_ENCRYPTOR_KEY_PAIR: 'base64-encoded-sodium-keypair'

    APP_SETTINGS_DATABASE_URL:     'mysql://user:pass@127.0.0.1:3306/starter'
    APP_SETTINGS_DATABASE_VERSION: '10.11'

    APP_SETTINGS_MAILER_DSN:       'smtp://127.0.0.1:25'

    APP_SETTINGS_CACHE_APP:           'cache.adapter.redis'
    APP_SETTINGS_REDIS_CACHE_HOST:    '127.0.0.1'
    APP_SETTINGS_REDIS_CACHE_PORT:    '6379'
    APP_SETTINGS_REDIS_CACHE_DB:      '0'

    APP_SETTINGS_SESSION_HANDLER:     'app.session.handler'
    APP_SETTINGS_REDIS_SESSION_HOST:  '127.0.0.1'
    APP_SETTINGS_REDIS_SESSION_PORT:  '6380'
    APP_SETTINGS_REDIS_SESSION_DB:    '0'

    ENV_HOST: 'your-domain.com'
```

## Redis

Two separate Redis instances are provisioned (both on `127.0.0.1`):

| Instance | Port | Purpose | Databases |
|---|---|---|---|
| `redis-server@cache` | `6379` | Application cache (`cache.adapter.redis`) | 2 |
| `redis-server@session` | `6380` | User sessions (`RedisSessionHandler`) | 1 |

Session keys are prefixed with `smp_`.

To enable Redis, set `APP_SETTINGS_CACHE_APP` to `cache.adapter.redis` and `APP_SETTINGS_SESSION_HANDLER` to `app.session.handler`, then fill in the host/port/db parameters.

## Generating the encryption key pair

The encryption key pair is required by SpipuCoreBundle. To generate one:

```bash
php -r "echo sodium_bin2base64(sodium_crypto_box_keypair(), SODIUM_BASE64_VARIANT_ORIGINAL);"
```

Store the output as `APP_SETTINGS_APP_ENCRYPTOR_KEY_PAIR`.

For tests, the key is generated automatically by `quality/phpunit.sh`.
