# Frontend

## Prerequisites

[Node.js](https://nodejs.org/) and [Yarn](https://yarnpkg.com/) must be installed.

## Commands

All commands must be run from the `website/` directory:

```bash
cd website/

yarn dev        # One-time development build
yarn watch      # Watch mode — rebuild on file change
yarn build      # Production build (minified, versioned)
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
| Swiper | 9 | Touch/carousel sliders |
| slim-select | 2 | Enhanced `<select>` component |
| Stimulus | 3 | Lightweight JS controllers |
| Webpack Encore | 5 | Build toolchain |
| Sass | 1 | CSS preprocessor |

## Entry point and output

| File | Role |
|---|---|
| `public/js/app.js` | Main webpack entry point |
| `public/scss/main.scss` | Application SCSS styles |
| `public/build/` | Compiled output (committed in production) |

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

// Application styles
import '../scss/main.scss'
```

`window.bootstrap`, `window.$` and `window.jQuery` **must** remain as globals — the Spipu bundle JS files depend on them.

## Webpack configuration

Key points in `webpack.config.js`:

- **Output**: `public/build/`
- **Source maps**: enabled in dev, disabled in prod
- **Versioning** (fingerprinting): enabled in prod only
- **Sass**: enabled via `enableSassLoader()`
- **jQuery 4 alias**: required to work around the `exports` field incompatibility with ProvidePlugin:

```js
config.resolve.alias = {
    'jquery$': path.resolve(__dirname, 'node_modules/jquery/dist/jquery.js'),
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

## Adding a new library

```bash
cd website/
yarn add <package-name>
```

Then import it in `public/js/app.js` or directly in the component that needs it.

## Adding SCSS styles

Edit `public/scss/main.scss` or create new partials and `@use` them from `main.scss`.
