const mix = require( 'laravel-mix' );

mix.options( {
    processCssUrls: false,
    terser: {
        extractComments: false,
    }
} )
    .sass('assets/sass/admin.scss', 'assets/css')
    .sass('assets/sass/admin-blocks.scss', 'assets/css')
    .sass('assets/sass/admin-global.scss', 'assets/css')
    .sass('assets/sass/front-end.scss', 'assets/css')
    .js('assets/js/src/admin/book-graphs.js', 'assets/js/build')
    .js('assets/js/src/admin/admin.js', 'assets/js/build')
    .js('assets/js/src/admin/admin-global.js', 'assets/js/build');
