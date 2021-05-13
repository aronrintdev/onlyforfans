/**
 * Laravel Mix Configuration file
 */
const mix = require('laravel-mix');
const path = require('path');
require('dotenv').config();
require('laravel-mix-bundle-analyzer');
// require('laravel-mix-alias');

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

/**
 * Alias to reduce clutter of `../..`s in js and vue imports
 */
mix.webpackConfig({
  resolve: {
    alias: {
      '@': path.resolve(__dirname, 'resources', 'assets', 'js'),
      '~': path.resolve(__dirname, 'resources', 'assets', 'sass'),
      '@components': path.resolve(__dirname, 'resources', 'assets', 'js', 'components'),
      '@views': path.resolve(__dirname, 'resources', 'assets', 'js', 'views'),
      '@plugins': path.resolve(__dirname, 'resources', 'assets', 'js', 'plugins'),
      '@routes': path.resolve(__dirname, 'resources', 'assets', 'js', 'routes'),
      '@helpers': path.resolve(__dirname, 'resources', 'assets', 'js', 'helpers'),
      '@mixins': path.resolve(__dirname, 'resources', 'assets', 'js', 'mixins'),
    },
  },
});

/**
 * Custom Vue Module loaders
 */
mix.extend('addWebpackLoaders', (webpackConfig, loaderRules) => {
  loaderRules.forEach((loaderRule) => {
    webpackConfig.module.rules.push(loaderRule);
  });
});

mix.addWebpackLoaders([
  {
    test: /\.(json5?|ya?ml)$/,
    type: 'javascript/auto',
    loader: '@intlify/vue-i18n-loader',
    include: [
      path.resolve(__dirname, 'resources', 'assets', 'js', 'locales')
    ],
  },
  {
    resourceQuery: /blockType=i18n/,
    type: 'javascript/auto',
    loader: '@intlify/vue-i18n-loader'
  },
  {
    resourceQuery: /blockType=scss/,
    type: 'javascript/auto',
    use: [ 'vue-style-loader', 'css-loader', 'sass-loader'],
  },
])

// Extract Important libraries to own files for load speed
mix.extract([
  'vue',
  'vuex',
  'axios',
  'lodash',
], 'public/js/vendor-1.js');
mix.extract([
  'bootstrap-vue'
], 'public/js/vendor-2.js');
mix.extract([
  '@fortawesome'
], 'public/js/vendor-3.js');
mix.extract([
  'video.js',
  'videojs-record'
], 'public/js/vendor-4.js');

// All other node_module libraries will end up in vendor.js
mix.extract('public/js/vendor.js');

if (mix.inProduction()) {
  mix.bundleAnalyzer({ analyzerMode: 'static', reportFilename: '../analyzerReport.html', openAnalyzer: false, generateStatsFile: true });
}

if (!mix.inProduction()) {
  mix.sourceMaps();
  if (mix.isWatching()) {
    if (!process.argv.includes('--hot')) {
      mix.bundleAnalyzer({ openAnalyzer: false, generateStatsFile: true });
    }
  }
}

/**
 * App
 */
mix.js('resources/assets/js/app.js', 'public/js/app.js')
  .sass('resources/assets/sass/app.scss', 'public/css/app.css')
  .vue({
    options: {
      loaders: {
        i18n: '@intlify/vue-i18n-loader',
      },
    },
  })

/**
 * Guest App
 */
mix.js('resources/assets/js/app.guest.js', 'public/js/app.guest.js')
  .vue({
    options: {
      loaders: {
        i18n: '@intlify/vue-i18n-loader',
      },
    },
  });

/**
 * Admin App
 */
mix.js('resources/assets/js/admin.app.js', 'public/js/admin.app.js')
  .vue({
    options: {
      loaders: {
        i18n: '@intlify/vue-i18n-loader',
      },
    },
  })
