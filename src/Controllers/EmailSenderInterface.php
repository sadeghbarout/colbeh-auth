<?php
/**
 * Created by PhpStorm.
 * User: sadegh
 * Date: 20/06/2021
 * Time: 10:37 AM
 */

namespace Colbeh\Auth\Controllers;


interface EmailSenderInterface {

	public static function sendValidation($email,$code);
	public static function sendForgetPass($email,$code);

}