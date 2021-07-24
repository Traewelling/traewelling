const mix = require("laravel-mix");

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

mix.js("resources/js/app.js", "public/js");
mix.js("resources/js/stats.js", "public/js");
mix.sass("resources/sass/app.scss", "public/css");
mix.js("resources/js/admin.js", "public/js");
mix.sass("resources/sass/vue.scss", "public/css").vue();
mix.js("resources/js/vue.js", "public/js").vue();
mix.sass("resources/sass/admin.scss", "public/css");
mix.sass("resources/sass/welcome.scss", "public/css");
mix.sourceMaps();

if (mix.inProduction()) {
    mix.version();
}
