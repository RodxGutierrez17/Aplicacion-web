<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Mockery\Generator\Method;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/available', function () {
    return('<h1> Available </h1>');
}) -> name('Available');

Route::any('/submit', function () {
    return 'submited';
});

Route::get('/test', function () {
    $url = Route('Available');
    return "<a href= '$url'> Click Here" ;
});

Route::get('/api/user', function(){
    return [
        'name' =>'Rodrigo Gutierrez',
        'email' => 'Rodrigo@gmail.com',
    ];
});

Route::any('/api/info', function(Request $request){
    return [
    'method' => $request -> method(),
    'url' => $request -> url(),
    'path' => $request -> path(),
    'header' => $request -> header(),
    'ip' => $request -> ip(),


    ];
});