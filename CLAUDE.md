# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Symfony 7.4 starter template (PHP 8.3+) with a custom suite of **Spipu bundles** providing core infrastructure: authentication, admin dashboard, background process management, configuration, and UI components.

The main application lives in `website/`. All quality/test tools run from the **repository root** (not from inside `website/`).

## Commands

### Assets (run from `website/`)
```bash
yarn dev        # Build assets for development
yarn watch      # Watch mode for continuous rebuilding
yarn build      # Production build with minification
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
~/install.sh
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
`.depfile.mvc.yaml` validates MVC separation. Layers: Command, Controller, Entity, Fixture, Form, Repository, Service, Step, Twig, Ui, Validator. Uses new deptrac format (`type: classLike`). Violations will fail `./quality/deptrac.sh`.

### Frontend
Webpack Encore with Stimulus.js (3.0), **Bootstrap 5.3**, **jQuery 4.0**, **FontAwesome 7**, SASS/SCSS support. Entry point: `public/js/app.js`.

**Notes importantes :**
- `window.bootstrap`, `window.$` et `window.jQuery` sont exposés globalement dans `app.js` — requis par les JS des bundles Spipu
- jQuery 4 nécessite un alias webpack dans `webpack.config.js` pour contourner le champ `exports` incompatible avec ProvidePlugin : `'jquery$': path.resolve(__dirname, 'node_modules/jquery/dist/jquery.js')`
- `bootstrap/dist/js/bootstrap.bundle` inclut Popper 2 — pas besoin d'installer `@popperjs/core` séparément

### Database & Cache
- **Production**: MariaDB 10.11+, Redis for sessions and cache (key prefix `spipu_`)
- **Tests**: SQLite (`var-test/test.sqlite`)
- **Fallback**: Filesystem cache in non-production environments

### Test Setup
Tests use Panther (browser testing with Firefox headless). Base class: `tests/WebTestCase.php`. Encryption key generated in `quality/phpunit.sh`. Test credentials and SQLite DB configured in `.env.test`.

**Prérequis locaux pour les tests fonctionnels (Panther) :**
```bash
sudo apt-get install php8.3-curl php8.3-cli php-xdebug php-pdo php-sqlite3
```

**Configuration Panther :** Les tests se connectent à `starter.lxd` via `external_base_uri`. Les capabilities Firefox (`acceptInsecureCerts`) sont passées dans `$managerOptions` (3ème paramètre de `createPantherClient`), pas dans `$options`.

## Key Configuration Files
- `website/.phpunit.xml` — PHPUnit 12 config (tests fonctionnels via Panther)
- `website/.phpcs.xml` — PSR12 code style rules
- `website/.phpmd.xml` — Mess detector rulesets
- `website/.phpqa.yml` — Quality tool orchestration
- `website/.depfile.mvc.yaml` — Deptrac MVC architecture rules (new classLike format)
- `website/config/packages/` — Bundle-specific Symfony config

## Dev Environment Details
- Public: `https://starter.lxd/`
- Admin: `https://starter.lxd/admin/` (login: `admin` / `password`)
- SSH users: `root` (service control only), `delivery` (composer, console)
- **Always use `www-data` user** when running `bin/console` in the dev environment
- Quality tools must run from the **host machine**, not inside the dev environment

## Coding Standards

### PHP & Symfony 7.4
- Target: **PHP 8.3**, **Symfony 7.4**, **Doctrine ORM 3**
- Every PHP file must have `declare(strict_types=1);` after `<?php`
- Always use `?Type $param = null` (never implicit nullable `Type $param = null`)
- Never use `$request->get()` — use `$request->query->get()` or `$request->request->get()`
- Use `#[Route(...)]` and `#[IsGranted(...)]` attributes (not annotations)
- Use `#[AsCommand(...)]` attribute on console commands
- All constants must have explicit visibility
- No `A|B` union types unless imposed by external contract — use `?type` or `mixed`
- `MicroKernelTrait::configureContainer()` est `private` en Symfony 7.4 — les sous-classes ne peuvent pas appeler `parent::configureContainer()`. Utiliser `build(ContainerBuilder $container)` à la place dans les Kernels de test.

### Frontend Libraries
- **Bootstrap 5.3** — use `data-bs-*` attributes, `me-/ms-/pe-/ps-*` spacing, `text-start/end`, `fw-bold`, `mb-3` (not `form-group`), `w-100` (not `btn-block`), `form-select` for selects, `text-bg-*` badges. Native `bootstrap.Modal` API (not jQuery `.modal()`)
- **jQuery 4.0** — no `$.proxy()`, `$.grep()` ; use `.bind()` et `Array.filter()`
- **FontAwesome 7** — préfixes `fa-solid`/`fa-regular`/`fa-brands` (pas `fas`/`far`/`fab`). Noms d'icônes : `house` (pas `home`), `screwdriver-wrench` (pas `tools`), `pen-to-square` (pas `edit`), `xmark` (pas `times`), `gear` (pas `cog`), `magnifying-glass` (pas `search`), `trash-can` (pas `trash-alt`), `right-to-bracket` (pas `sign-in-alt`), `right-from-bracket` (pas `sign-out-alt`), `rotate` (pas `sync-alt`), `triangle-exclamation` (pas `exclamation-triangle`). Dans les appels PHP `setIcon()`, le nom est passé sans le préfixe `fa-`

### PHPUnit 12
- Pas de `<listeners>` dans phpunit.xml (supprimé en PHPUnit 10+)
- Utiliser `<bootstrap class="..."/>` dans `<extensions>` pour les extensions
- Ne pas utiliser les API dépréciées : `withConsecutive()`, `willReturnOnConsecutiveCalls()` → utiliser `willReturnCallback()` avec compteur
- `$this->returnArgument()` / `$this->returnCallback()` → `->willReturnArgument()` / `->willReturnCallback()`
- Les bundles Spipu créent des mocks sans expectations → ajouter `#[AllowMockObjectsWithoutExpectations]` sur les classes de test concrètes (pas seulement la classe parente)
- Pour afficher le détail des notices PHPUnit : `--display-phpunit-notices` (pas `--display-notices`)