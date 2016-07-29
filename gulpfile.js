var
    elixir = require('laravel-elixir'),
    elixirTypscript = require('elixir-typescript'),
    nodePath = '../../../node_modules/';

elixir(function(mix) {
    mix
        .less('aitemu/master.less', 'public/css/aitemu.css')
        .less('yuuno/master.less', 'public/css/yuuno.css')
        .typescript('master/**/*.ts', 'public/js/master.js')
        .typescript('aitemu/**/*.ts', 'public/js/aitemu.js')
        .typescript('yuuno/**/*.ts', 'public/js/yuuno.js')
        .scripts([
            nodePath + 'turbolinks/dist/turbolinks.js',
        ], 'public/js/libraries.js');
});
