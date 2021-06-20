<?php
use Illuminate\Support\Facades\Route;

Route::group(['namespace' => 'Colbeh\Auth\Controllers', 'prefix' => 'auth'], function () {
//	Route::get('/', ['as' => 'bmi_path', 'uses' => 'ConstController@index']);

	Route::get('login2/{phone}', "AuthController@loginOrSignUp");


	Route::get('login/{username}/{pass}', "AuthController@login");
	Route::get('signUp/{username}/{pass}/{phone?}', "AuthController@signUp");

	Route::get('validate/{username}/{code}', "AuthController@validate");
	Route::get('resendCode/{username}', "AuthController@resendCode");

	Route::get('forgetPass/{username}', "AuthController@forgetPass");
	Route::get('recoveryPass/{username}/{code}/{newPass?}', "AuthController@recoveryPass");
//	Route::post('/add', "ConstController@add");
//	Route::post('/column', "ConstController@columnAdd");
});