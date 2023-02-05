let mix = require('laravel-mix');

// Frontend.
mix.js(['src/js/main.js'], 'assets/js/mdjm-plugin.js');
mix.sass('src/sass/main.scss', 'assets/css/mdjm-plugin.css');
mix.minify(['assets/css/mdjm-plugin.css', 'assets/js/mdjm-plugin.js']);

// Admin.
mix.js(['src/admin/js/admin.js'], 'assets/js/mdjm-admin.js');
mix.sass('src/admin/sass/admin.scss', 'assets/css/mdjm-admin.css');
mix.minify(['assets/css/mdjm-admin.css', 'assets/js/mdjm-admin.js']);