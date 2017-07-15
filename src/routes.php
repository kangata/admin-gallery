<?php

$admin = [
    'prefix' => 'admin',
    'namespace' => 'QuetzalArc\Admin\Gallery',
];

Route::group($admin, function () {
    Route::get('/albums', 'AlbumController@index')->name('albums.index');
    Route::get('/albums/create', 'AlbumController@create')->name('albums.create');
    Route::post('/albums', 'AlbumController@store')->name('albums.store');
    Route::get('/albums/{id}', 'AlbumController@show')->name('albums.show');
    Route::get('/albums/{id}/edit', 'AlbumController@edit')->name('albums.edit');
    Route::patch('/albums/{id}', 'AlbumController@update')->name('albums.update');
    Route::delete('/albums/{id}', 'AlbumController@delete')->name('albums.delete');

    Route::post('/albums/{id}/upload', 'AlbumController@upload')->name('albums.upload');
    Route::post('/albums/{id}/multi-upload', 'AlbumController@multiUpload')->name('albums.multi-upload');

    Route::patch('/albums/{album_id}/change-cover', 'AlbumController@changeCover')->name('albums.change-cover');
    Route::delete('/albums/{album_id}/photos/{photo_id}', 'AlbumController@deletePhoto')->name('albums.delete-photo');

    Route::get('/images/photos/{filename}', 'AlbumController@assetPhoto')->name('asset.photo');
});