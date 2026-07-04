import Encore from '@symfony/webpack-encore';
import path from 'path';

// Manually configure the runtime environment if not already configured yet by the "encore" command.
// It's useful when you use tools that rely on webpack.config.mjs file.
if (!Encore.isRuntimeEnvironmentConfigured()) {
    Encore.configureRuntimeEnvironment(process.env.NODE_ENV || 'dev');
}

Encore
    .autoProvidejQuery()
    // directory where compiled assets will be stored
    .setOutputPath('public/build/')
    // public path used by the web server to access the output path
    .setPublicPath('/build')
    // only needed for CDN's or subdirectory deploy
    //.setManifestKeyPrefix('build/')

    /*
     * ENTRY CONFIG
     *
     * Each entry will result in one JavaScript file (e.g. app.js)
     * and one CSS file (e.g. app.css) if your JavaScript imports CSS.
     */
    .addEntry('app',     './public/js/app.js')

    // When enabled, Webpack "splits" your files into smaller pieces for greater optimization.
    .splitEntryChunks()

    // will require an extra script tag for runtime.js
    // but, you probably want this, unless you're building a single-page app
    .enableSingleRuntimeChunk()

    /*
     * FEATURE CONFIG
     *
     * Enable & configure other features below. For a full
     * list of features, see:
     * https://symfony.com/doc/current/frontend.html#adding-more-features
     */
    .cleanupOutputBeforeBuild()
    .enableSourceMaps(!Encore.isProduction())
    // enables hashed filenames (e.g. app.abc123.css)
    .enableVersioning(Encore.isProduction())

    // configure Babel — polyfills core-js à la demande (remplace l'option
    // useBuiltIns/corejs de preset-env, supprimée dans Babel 8)
    .configureBabel((babelConfig) => {
        babelConfig.plugins.push([
            'babel-plugin-polyfill-corejs3',
            { method: 'usage-global', version: '3.49' },
        ]);
    })

    // enables Sass/SCSS support
    .enableSassLoader()

    // uncomment if you use TypeScript
    //.enableTypeScriptLoader()

    // uncomment if you use React
    //.enableReactPreset()

    // uncomment to get integrity="..." attributes on your script & link tags
    // requires WebpackEncoreBundle 1.4 or higher
    //.enableIntegrityHashes(Encore.isProduction())

    // uncomment if you're having problems with a jQuery plugin
    //.autoProvideVariables({
    //    $: 'jquery',
    //    jQuery: 'jquery',
    //    'window.jQuery': 'jquery',
    //})


// uncomment if you use API Platform Admin (composer req api-admin)
//.enableReactPreset()
//.addEntry('admin', './assets/js/admin.js')
;

const config = await Encore.getWebpackConfig();

// Alias jQuery 4 vers le fichier CJS pour contourner le champ "exports"
// qui bloque ProvidePlugin avec le chemin ./dist/jquery.js
config.resolve.alias = {
    ...config.resolve.alias,
    'jquery$': path.resolve(import.meta.dirname, 'node_modules/jquery/dist/jquery.js'),
};

export default config;
