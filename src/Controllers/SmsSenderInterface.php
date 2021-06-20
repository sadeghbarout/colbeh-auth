<?php
/**
 * Created by PhpStorm.
 * User: sadegh
 * Date: 20/06/2021
 * Time: 10:37 AM
 */

namespace Colbeh\Auth\Controllers;


interface SmsSenderInterface {

	public static function sendValidation($phone,$code);
	public static function sendForgetPass($phone,$code);

}