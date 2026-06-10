<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;

Route::get('/', function () {
    return response()->redirectTo('admin');
});

Route::get('/test-dropbox', function () {
    try {
        Storage::disk('dropbox')->put(sprintf('test_%s.txt', date('Y-m-d_H.i.s')), 'Ciao Dropbox!');

        return 'File caricato con successo!';
    } catch (Exception $e) {
        return 'Errore: ' . $e->getMessage();
    }
});
