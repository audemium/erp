<?php

use Illuminate\Support\Facades\Route;

Route::view('/', 'dashboard');

Route::group(['namespace' => 'App\Http\Controllers'], function () {
	Route::get('/employees', 'EmployeeController@list');
	Route::get('/employees/{id}', 'EmployeeController@show')->name('employees.show');
});
