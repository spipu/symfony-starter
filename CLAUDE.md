# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Symfony 6.4 starter template (PHP 8.1+) with a custom suite of **Spipu bundles** providing core infrastructure: authentication, admin dashboard, background process management, configuration, and UI components.

The main application lives in `website/`. All quality/test tools run from the **repository root** (not from inside `website/`).

## Commands

### Assets (run from `website/`)
```bash
npm run dev        # Build assets for development
npm run watch      # Watch mode for continuous rebuilding
npm run build      # Production build with minification
```

### Tests (run from repo root)
```bash
./quality/phpunit.sh             # Run PHPUnit test suite
./quality/phpunit.sh --coverage  # Run with coverage (opens Firefox report)
```

### Code Quality (run from repo root)
```bash
./quality/analyze.sh   # phpcs + phpmd + phpcpd + phploc + phpmetrics + pdepend
./quality/deptrac.sh   # Architecture layer dependency validation
```

### Symfony Console (on dev env)
```bash
# Always run as www-data inside the dev environment
ssh delivery@starter.lxd
cd /var/www/starter/website/
sudo -u www-data bin/console <command>
```

### Dev Environment
```bash
./architecture/create-lxd.sh     # Create LXD dev environment
./architecture/create-docker.sh  # Create Docker dev environment
./architecture/start-docker.sh   # Start Docker dev environment
```

### After pull/rebase
```bash
ssh delivery@starter.lxd
/var/www/starter/architecture/scripts/install.sh
```

## Architecture

### Spipu Bundle Stack
The application is built around custom Spipu bundles (all in `vendor/spipu/`):
- **SpipuCoreBundle** — encryption, core utilities
- **SpipuUiBundle** — UI components, menu system, grid/filter system
- **SpipuUserBundle** — entity-based authentication with remember-me, role management
- **SpipuConfigurationBundle** — runtime configuration management
- **SpipuProcessBundle** — background process/job execution
- **SpipuDashboardBundle** — admin dashboard widgets

Application code in `src/` primarily wires these bundles together via:
- `src/Service/MenuDefinition.php` — defines admin menu structure
- `src/Service/RoleDefinition.php` — registers application roles
- `src/Service/MailConfiguration.php` — mailer setup
- `src/Entity/AdminUser.php` — extends SpipuUserBundle's user entity

### Architectural Layers (enforced by Deptrac)
`.depfile.mvc.yaml` validates MVC separation. Layers: Controller, Entity, Fixture, Form, Repository, Service, Twig, Validator. Violations will fail `./quality/deptrac.sh`.

### Frontend
Webpack Encore with Stimulus.js (3.0), Bootstrap 4+5 (both installed), jQuery 3.7, SASS/SCSS support. Entry point: `public/js/app.js`.

### Database & Cache
- **Production**: MariaDB 10.4+, Redis for sessions and cache (key prefix `spipu_`)
- **Tests**: SQLite (in-memory or `var-test/test.sqlite`)
- **Fallback**: Filesystem cache in non-production environments

### Test Setup
Tests use Panther (browser testing with Firefox headless). The bootstrap at `tests/bootstrap.php` generates encryption keys and clears the test cache. Base class: `tests/WebTestCase.php`. Test credentials and SQLite DB configured in `.env.test`.

## Key Configuration Files
- `website/.phpunit.xml` — PHPUnit config with Panther server extension
- `website/.phpcs.xml` — PSR12 code style rules
- `website/.phpmd.xml` — Mess detector rulesets
- `website/.phpqa.yml` — Quality tool orchestration
- `website/.depfile.mvc.yaml` — Deptrac MVC architecture rules
- `website/config/packages/` — Bundle-specific Symfony config

## Dev Environment Details
- Public: `https://starter.lxd/`
- Admin: `https://starter.lxd/admin/` (login: `admin` / `password`)
- SSH users: `root` (service control only), `delivery` (composer, console)
- **Always use `www-data` user** when running `bin/console` in the dev environment
- Quality tools must run from the **host machine**, not inside the dev environment