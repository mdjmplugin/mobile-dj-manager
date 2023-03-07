let mix = require('laravel-mix');



// Frontend.
// mix.js(['src/js/main.js'], 'assets/js/mdjm-plugin.min.js');
mix.sass('src/sass/main.scss', 'assets/css/mdjm-plugin.min.css');
// mix.minify(['assets/css/mdjm-plugin.css', 'assets/js/mdjm-plugin.min.js']);

// Admin.
// mix.js(['src/admin/js/admin.js'], 'assets/js/mdjm-admin.min.js');
mix.sass('src/admin/sass/admin.scss', 'assets/css/mdjm-admin.min.css');
// mix.minify(['assets/css/mdjm-admin.css', 'assets/js/mdjm-admin.min.js']);

mix.js(['src/js/mdjm-ajax.js'], 'assets/js/mdjm-ajax.min.js');