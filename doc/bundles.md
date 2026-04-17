# Architecture en bundles

Le projet est découpé en bundles perso sous le vendor `Laurent`.

## Structure

```
website/src/
├── App/                                  # Code applicatif résiduel
│   ├── Fixture/                          # Fixtures (AdminUsers, AdminConfigurations)
│   └── Kernel.php
└── Laurent/
    ├── CoreBundle/                       # Socle bas niveau, sans dépendance inter-bundle
    │   ├── config/services.yaml
    │   ├── src/
    │   │   ├── Controller/AbstractController.php
    │   │   ├── Entity/AdminUser.php
    │   │   ├── Service/{FileService,HostService,RedisClient}.php
    │   │   ├── Twig/UiExtension.php
    │   │   └── LaurentCoreBundle.php
    │   └── templates/                    # translator.html.twig, grid/…
    └── BackOfficeBundle/                 # UI admin (dépend de Core)
        ├── config/{routes.yaml,services.yaml}
        ├── src/
        │   ├── Controller/MainController.php  # admin_home = /
        │   ├── Service/{AdminRoleDefinition,MailConfiguration,MenuDefinition}.php
        │   └── LaurentBackOfficeBundle.php    # déclare getRolesHierarchy()
        └── templates/main/home.html.twig
```

## Namespaces PSR-4

Défini dans `website/composer.json` :

```json
"autoload": {
    "psr-4": {
        "App\\": "src/App/",
        "Laurent\\BackOfficeBundle\\": "src/Laurent/BackOfficeBundle/src/",
        "Laurent\\CoreBundle\\":       "src/Laurent/CoreBundle/src/"
    }
}
```

Chaque bundle a son propre sous-dossier `src/` qui contient le namespace racine `Laurent\XxxBundle\`.

## Bundles Symfony

Enregistrés dans `website/config/bundles.php` :

```php
Laurent\CoreBundle\LaurentCoreBundle::class       => ['all' => true],
Laurent\BackOfficeBundle\LaurentBackOfficeBundle::class => ['all' => true],
```

Chaque classe de bundle étend `Spipu\CoreBundle\AbstractBundle`, qui importe
automatiquement `../config/services.yaml` du bundle via `loadExtension()`.

Le `LaurentBackOfficeBundle` surcharge `getRolesHierarchy()` pour retourner
`AdminRoleDefinition` (ancien `App\Service\RoleDefinition`, avec son ancien nom).

## Routes

`website/config/routes.yaml` importe les routes du bundle :

```yaml
laurent_back_office:
    resource: '@LaurentBackOfficeBundle/config/routes.yaml'
```

Le BackOfficeBundle charge ses propres contrôleurs via attributs PHP 8 :

```yaml
# src/Laurent/BackOfficeBundle/config/routes.yaml
controllers:
    resource: '../src/Controller/'
    type:     attribute
    prefix:   /
    trailing_slash_on_root: false
```

La route racine `/` s'appelle désormais `admin_home` (ex-`app_home`).

## Templates

Chaque bundle expose ses templates via un namespace Twig dérivé de la classe :

- `LaurentBackOfficeBundle` → `@LaurentBackOffice/…`
- `LaurentCoreBundle` → `@LaurentCore/…`

Trois emplacements coexistent :

| Template | Emplacement | Raison |
|----------|-------------|--------|
| `templates/base.html.twig` | racine | extend-é sans namespace par les templates Spipu (user login/profile) |
| `templates/base_email.html.twig` | racine | symétrie avec base.html.twig |
| `@LaurentCore/translator.html.twig` | CoreBundle | partagé, bas niveau |
| `@LaurentCore/grid/…` | CoreBundle | overrides de grid Spipu |
| `@LaurentBackOffice/main/home.html.twig` | BackOfficeBundle | page admin home |
| `templates/bundles/TwigBundle/Exception/…` | racine | overrides Symfony TwigBundle |

**Pourquoi `base.html.twig` en racine ?** Les templates Spipu User (login,
profile) font `{% extends 'base.html.twig' %}` sans préfixe ; Twig résout ça
dans le `default_path` (`templates/`). Le déplacer dans `@LaurentBackOffice`
casse le login avec `Twig\Error\LoaderError`.

## ide-twig.json

Déclare tous les namespaces Twig pour l'auto-complétion IDE (PhpStorm Symfony
plugin). À mettre à jour quand un bundle ajoute son dossier `templates/`.

## Fichier `ide-twig.json`

```json
{
  "namespaces": [
    { "namespace": "LaurentBackOffice", "path": "website/src/Laurent/BackOfficeBundle/templates" },
    { "namespace": "LaurentCore",       "path": "website/src/Laurent/CoreBundle/templates" },
    { "namespace": "SpipuConfiguration", "path": "website/vendor/spipu/configuration-bundle/templates" },
    { "namespace": "SpipuDashboard",     "path": "website/vendor/spipu/dashboard-bundle/templates" },
    { "namespace": "SpipuProcess",       "path": "website/vendor/spipu/process-bundle/templates" },
    { "namespace": "SpipuUi",            "path": "website/vendor/spipu/ui-bundle/templates" },
    { "namespace": "SpipuUser",          "path": "website/vendor/spipu/user-bundle/templates" }
  ]
}
```

## Règles de dépendance entre bundles

Validées par `website/.depfile.bundles.yaml` :

- `CoreBundle` ne dépend d'aucun autre bundle Laurent
- `BackOfficeBundle` peut dépendre de `CoreBundle` (c'est la couche UI)

Un nouveau bundle métier (Slack, Sso, Hub, …) :
1. S'ajoute dans `.depfile.bundles.yaml` avec ses dépendances explicites
2. N'est jamais autorisé à dépendre de `BackOfficeBundle`

## Ajouter un nouveau bundle

1. Créer `website/src/Laurent/XxxBundle/{config,src,templates}/`
2. Classe `src/LaurentXxxBundle.php` qui étend `Spipu\CoreBundle\AbstractBundle`
3. Fichier `config/services.yaml` avec le pattern générique :
   ```yaml
   services:
       _defaults: { autowire: true, autoconfigure: true }
       Laurent\XxxBundle\:
           resource: '../src/*'
           exclude: '../src/{Entity,Tests,LaurentXxxBundle.php}'
   ```
4. Enregistrer dans `website/config/bundles.php`
5. Ajouter le mapping PSR-4 dans `website/composer.json`
6. Mettre à jour `ide-twig.json`
7. Ajouter les couches + règles dans `website/.depfile.bundles.yaml`
8. Si le bundle expose des routes : ajouter `config/routes.yaml` et l'importer
   dans `website/config/routes.yaml`
