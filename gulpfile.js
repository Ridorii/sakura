var
    elixir = require('laravel-elixir'),
    elixirTypscript = require('elixir-typescript'),
    nodePath = '../../../node_modules/';

elixir(function(mix) {
    mix
        .less('app.less')
        .typescript('**/*.ts', 'public/js/app.js')
        .scripts([
            nodePath + 'turbolinks/dist/turbolinks.js'
        ], 'public/js/libs.js');
});
