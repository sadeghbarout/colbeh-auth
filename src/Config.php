<?php
/**
 * Created by PhpStorm.
 * User: sadegh
 * Date: 20/06/2021
 * Time: 11:20 AM
 */

namespace Colbeh\Auth;


class Config {

	public static function tryCount() {return config('auth_colbeh.config_try_count');}
	public static function otpLifeTime() {return config('auth_colbeh.config_otp_life_time');}
	public static function providers() {return config('auth_colbeh.providers');}

	public static function needValidation($provider) {return $provider['need_validation']; }
	public static function type($provider) {return $provider['type']; }
	public static function model($provider) {return $provider['model']; }



}