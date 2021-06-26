<?php
/**
 * Created by PhpStorm.
 * User: sadegh
 * Date: 20/06/2021
 * Time: 11:20 AM
 */

namespace Colbeh\Auth;


class Col {

	public static function username() {return config('auth_colbeh.COL_USERNAME');}
	public static function password() {return config('auth_colbeh.COL_PASSWORD');}
	public static function phoneNumberOrEmail() {return config('auth_colbeh.COL_PHONE_NUMBER_OR_EMAIL');}
	public static function otp() {return config('auth_colbeh.COL_OTP');}
	public static function otpTryCount() {return config('auth_colbeh.COL_OTP_TRY_COUNT');}
	public static function otpLastDate() {return config('auth_colbeh.COL_OTP_LAST_DATE');}
	public static function validationStatus() {return config('auth_colbeh.COL_VALIDATION_STATUS');}


	public static function enumNotValidated() {return 'not_validated';}
	public static function enumValidated() {return 'validated';}
}