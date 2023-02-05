let mix = require('laravel-mix');
mix.js(['src/js/main.js'], 'assets/js/mdjm-plugin.js');
mix.sass('src/sass/main.scss', 'assets/css/mdjm-plugin.css');
mix.minify(['assets/css/mdjm-plugin.css', 'assets/js/mdjm-plugin.js']);