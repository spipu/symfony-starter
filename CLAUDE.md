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

**IMPORTANT — Claude ne lance JAMAIS `phpunit.sh`** : le script écrit dans `/etc/hosts` via `sudo` et doit rester à la main de l'utilisateur. Pour vérifier la santé du code, utilise `cache:warmup`, `debug:container`, `debug:router` en SSH sur la LXD. **Quand le code est prêt, dire explicitement à l'utilisateur** qu'il peut lancer `./quality/phpunit.sh` — ne pas l'attendre silencieusement.

**Browser de test** : par défaut Firefox headless. Si Claude est redémarré avec `--chrome` (ou si l'utilisateur mentionne Chrome), basculer les tests Panther sur Chrome (voir `tests/WebTestCase.php` — `'browser' => self::FIREFOX` → `self::CHROME`).

### Code Quality (run from repo root) — obligatoire après TOUT développement
```bash
./quality/deptrac.sh   # 3 fichiers deptrac : .global, .mvc, .bundles
./quality/analyze.sh   # phpcs + phpmd + phpcpd + phploc + phpmetrics + pdepend
```

Claude **doit lancer deptrac.sh et analyze.sh** systématiquement après chaque dev (ils ne demandent pas sudo et s'exécutent côté hôte). Régler tout violation avant d'annoncer la tâche terminée.

### Composer (toujours depuis la LXD)
```bash
ssh delivery@starter.lxd "cd /var/www/starter/website && composer <command>"
```

Ne **jamais** lancer composer depuis l'hôte — versions PHP/extensions peuvent différer, et le `composer.lock` doit rester cohérent avec l'environnement runtime.

### Symfony Console (on dev env)
```bash
# Always run as www-data inside the dev environment
ssh delivery@starter.lxd
cd /var/www/starter/website/
sudo -u www-data bin/console <command>
```

Pour les vérifs one-shot depuis l'hôte (sans session interactive) :
```bash
ssh delivery@starter.lxd "cd /var/www/starter/website && sudo -u www-data bin/console <cmd>"
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

### Spipu Bundle Stack (vendor)
Bundles tiers installés via composer dans `vendor/spipu/` :
- **SpipuCoreBundle** — encryption, core utilities, `AbstractBundle`, `Environment`
- **SpipuUiBundle** — UI components, menu system (via `spipu.ui.service.menu_definition`), grid/filter
- **SpipuUserBundle** — entity-based authentication with remember-me, role management
- **SpipuConfigurationBundle** — runtime configuration management
- **SpipuProcessBundle** — background process/job execution
- **SpipuDashboardBundle** — admin dashboard widgets

### Bundles applicatifs (`src/Laurent/`)
Le code applicatif est découpé en bundles sous le vendor `Laurent`. Voir
`doc/bundles.md` pour le détail.

- **`Laurent\CoreBundle`** (`src/Laurent/CoreBundle/`) — socle bas niveau sans
  dépendance inter-bundle : `AdminUser` entity, `FileService`, `HostService`,
  `RedisClient`, `UiExtension` (Twig), `AbstractController`.
  Templates : `@LaurentCore/translator.html.twig`, `@LaurentCore/grid/…`.
- **`Laurent\BackOfficeBundle`** (`src/Laurent/BackOfficeBundle/`) — UI admin
  qui dépend de Core : `MainController` (route `admin_home` = `/`),
  `MenuDefinition`, `AdminRoleDefinition` (tag `spipu.user.role`),
  `MailConfiguration` (implémente `MailConfigurationInterface`).
  La classe de bundle surcharge `getRolesHierarchy()`.

**`src/App/`** ne contient plus que le `Kernel.php` et les `Fixture/` — les
entités, services et contrôleurs sont dans les bundles Laurent.

### Templates — règle cruciale
`templates/base.html.twig` et `templates/base_email.html.twig` **doivent rester
à la racine** (pas dans un bundle), car les templates Spipu User
(`@SpipuUser/login.html.twig`, `@SpipuUser/profile/*.html.twig`) font
`{% extends 'base.html.twig' %}` sans préfixe. Twig les résout via le
`twig.default_path` (`templates/`). Déplacer `base.html.twig` casse le login
avec `Twig\Error\LoaderError`.

Les templates spécifiques à un bundle (home admin, etc.) utilisent le
namespace Twig du bundle : `@LaurentBackOffice/main/home.html.twig`.

### Architectural Layers (enforced by Deptrac)
Trois fichiers deptrac complémentaires (pattern mind) :
- `.depfile.global.yaml` — couches globales `App` → `Laurent`
- `.depfile.mvc.yaml` — couches MVC catch-all (`.*\\Service\\.*`, etc.)
- `.depfile.bundles.yaml` — dépendances inter-bundles Laurent

Règles pour nouveaux bundles métier : ne jamais dépendre de `BackOfficeBundle`.
`BackOfficeBundle` est la couche UI — elle peut dépendre de tous les autres.

### Enregistrement d'un bundle Laurent
1. Classe `Laurent\XxxBundle\LaurentXxxBundle extends Spipu\CoreBundle\AbstractBundle`
   — `AbstractBundle::loadExtension()` importe automatiquement
   `../config/services.yaml` relatif au dossier `src/`.
2. PSR-4 dans `composer.json` : `"Laurent\\XxxBundle\\": "src/Laurent/XxxBundle/src/"`
3. Enregistrement dans `config/bundles.php`
4. Routes du bundle dans `config/routes.yaml` importées depuis
   `website/config/routes.yaml` via `@LaurentXxxBundle/config/routes.yaml`
5. `ide-twig.json` si le bundle a des templates
6. Couches + règles dans `.depfile.bundles.yaml`

### Frontend
Webpack Encore with Stimulus.js (3.0), **Bootstrap 5.3**, **jQuery 4.0**, **FontAwesome 7**, SASS/SCSS support. Entry point: `public/js/app.js`.

**Notes importantes :**
- `window.bootstrap`, `window.$` et `window.jQuery` sont exposés globalement dans `app.js` — requis par les JS des bundles Spipu
- `defer: false` dans `webpack_encore.yaml` est **obligatoire** — les bundles Spipu injectent des scripts inline dans le HTML qui utilisent `translator`, `$`, `bootstrap` comme variables globales ; ces scripts s'exécutent avant un script différé, ce qui cause `ReferenceError: translator is not defined`
- jQuery 4 nécessite un alias webpack dans `webpack.config.js` pour contourner le champ `exports` incompatible avec ProvidePlugin : `'jquery$': path.resolve(__dirname, 'node_modules/jquery/dist/jquery.js')`
- `bootstrap/dist/js/bootstrap.bundle` inclut Popper 2 — pas besoin d'installer `@popperjs/core` séparément

### Configuration System

Le projet **n'utilise pas `.env`** pour la configuration de production. À la place :
- `config/app_default_configuration.yaml` — valeurs par défaut (SQLite, secrets temporaires, filesystem cache). Sert de repli pour les tests et le dev local.
- `/etc/starter/symfony.yaml` — fichier **hors dépôt**, déployé par l'hébergeur, contient les vraies valeurs (DB, Redis, secrets). Chargé via `imports` dans `framework.yaml` avec `ignore_errors: true`.

Tous les paramètres suivent la convention `APP_SETTINGS_*` et sont référencés dans les configs Symfony via `%APP_SETTINGS_XXX%`.

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
- Admin: `https://starter.lxd/admin/` (login: `spipu` / `password`)
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
- `MicroKernelTrait::configureContainer()` est `private` en Symfony 7.4. Pour permettre à un Kernel de test d'appeler `parent::configureContainer()`, le Kernel applicatif (`src/Kernel.php`) doit l'override explicitement en `protected` (et ré-importer les YAML dedans). Ne pas utiliser `build()` pour override des paramètres : il s'exécute **avant** le chargement des YAML, donc tout `parameterBag->set()` y est silencieusement écrasé par les valeurs des YAML chargées ensuite (ex. `APP_SETTINGS_APP_CODE='dev'` écrasé par la valeur `'prod'` de `app_default_configuration.yaml`).

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