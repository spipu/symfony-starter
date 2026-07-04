# Frontend

## Prerequisites

Node.js (≥ 24, ships with npm) is provisioned in the dev environment. npm commands
must always be run **inside the LXD** (reference Node version, lockfile consistent
with the runtime) — never from the host.

## Commands

```bash
ssh delivery@starter.lxd
cd /var/www/starter/website/

npm run dev     # One-time development build
npm run watch   # Watch mode — rebuild on file change
npm run build   # Production build (minified, versioned)
```

Or via the architecture script (from the dev environment):

```bash
ssh delivery@starter.lxd
/var/www/starter/architecture/scripts/watch-front.sh
```

## Libraries

| Library | Version | Role |
|---|---|---|
| Bootstrap | 5.3 | CSS framework + JS components |
| jQuery | 4.0 | DOM manipulation, required by Spipu UI bundles |
| FontAwesome | 7 | Icons |
| slim-select | 4 | Enhanced `<select>` component |
| Webpack Encore | 7 | Build toolchain (ESM-only, requires Babel 8) |
| Sass | 1 | CSS preprocessor |

## Entry point and output

| File | Role |
|---|---|
| `public/js/app.js` | Main webpack entry point |
| `public/scss/main.scss` | Application SCSS styles |
| `public/build/` | Compiled output (gitignored — built by `install.sh` / on deploy) |

### `app.js` — what it imports

```js
import $ from 'jquery';
import * as bootstrap from 'bootstrap/dist/js/bootstrap.bundle';

// Required globals for Spipu bundles
window.bootstrap = bootstrap;
window.$ = $;
window.jQuery = $;

// Spipu UI components
import '../bundles/spipuui/css/spipu-ui.css'
import '../bundles/spipuui/js/spipu-...'

// App components and styles
import './slim-selects'
import '../scss/main.scss'
```

`window.bootstrap`, `window.$` and `window.jQuery` **must** remain as globals — the Spipu bundle JS files depend on them.

## Webpack configuration

Key points in `webpack.config.mjs` (ESM — Encore 7 no longer supports CommonJS configs, and `Encore.getWebpackConfig()` is async, hence the top-level `await`):

- **Output**: `public/build/`
- **Source maps**: enabled in dev, disabled in prod
- **Versioning** (fingerprinting): enabled in prod only
- **Sass**: enabled via `enableSassLoader()`
- **Polyfills**: Babel 8 removed `useBuiltIns`/`corejs` from preset-env; polyfills are provided by `babel-plugin-polyfill-corejs3` (`method: 'usage-global'`) pushed via `configureBabel()`
- **jQuery 4 alias**: required to work around the `exports` field incompatibility with ProvidePlugin:

```js
config.resolve.alias = {
    'jquery$': path.resolve(import.meta.dirname, 'node_modules/jquery/dist/jquery.js'),
};
```

Do not remove this alias — without it, jQuery ProvidePlugin breaks silently.

## Bootstrap 5 usage rules

- Spacing: `me-/ms-/pe-/ps-*`, `mb-3`, `w-100`
- Text: `text-start/end`, `fw-bold`, `text-bg-*`
- Badges: `text-bg-*` (not `badge-*`)
- Selects: `form-select` (not `custom-select`)
- Modals: use native `bootstrap.Modal` API (not jQuery `.modal()`)
- Attributes: `data-bs-*` (not `data-*`)

## FontAwesome 7 usage rules

Prefixes: `fa-solid`, `fa-regular`, `fa-brands` (not `fas`, `far`, `fab`).

Common icon name changes:

| Old name | New name |
|---|---|
| `home` | `house` |
| `edit` | `pen-to-square` |
| `times` | `xmark` |
| `cog` | `gear` |
| `search` | `magnifying-glass` |
| `trash-alt` | `trash-can` |
| `sign-in-alt` | `right-to-bracket` |
| `sign-out-alt` | `right-from-bracket` |
| `sync-alt` | `rotate` |
| `exclamation-triangle` | `triangle-exclamation` |
| `tools` | `screwdriver-wrench` |

In PHP `setIcon()` calls, pass the icon name without the `fa-` prefix.

## slim-select (v4)

`public/js/slim-selects.js` wraps every `select.slim-select` in a SlimSelect instance
(placeholder support, translated search texts, accent-insensitive filter, red border
on empty required multi-selects). v4 specifics:

- `searchPlaceholder`/`searchText` live under `settings:` (not at the root of the options)
- CSS is imported via `import 'slim-select/styles'`
- The native `<select>` is reachable via `instance.selectEl`; the visible container is `.ss-main`

## Adding a new library

```bash
ssh delivery@starter.lxd
cd /var/www/starter/website/
npm install <package-name>
```

Then import it in `public/js/app.js` or directly in the component that needs it.

## Adding SCSS styles

Edit `public/scss/main.scss` or create new partials and `@use` them from `main.scss`.
