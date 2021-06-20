<?php
/**
 * Created by PhpStorm.
 * User: sadegh
 * Date: 20/06/2021
 * Time: 02:44 PM
 */

namespace Colbeh\Auth;


use Colbeh\Auth\Controllers\EmailSenderInterface;
use Colbeh\Auth\Controllers\SmsSenderInterface;

class Helper {

	public static function getSender($phoneOrEmail) {
		if (strpos($phoneOrEmail, '@') != false)
			return self::getEmailSender();
		else
			return self::getSMSSender();
	}

	public static function getSMSSender(): SmsSenderInterface {
		$smsSender = config('auth_colbeh.SMSSenderClass');
		$smsSender = new $smsSender;
		return $smsSender;
	}

	public static function getEmailSender(): EmailSenderInterface {
		$emailSender = config('auth_colbeh.EmailSenderClass');
		$emailSender = new $emailSender;
		return $emailSender;
	}


	public static function generatePassword() {
		$alphabet = "123456789abcdefghijklmnopqrstuvwxyz"; //used chars
		$codeLenght = 6;

		return self::generateString($alphabet, $codeLenght);
	}

	public static function generateOtpCode() {
		$alphabet = "123456789"; //used chars
		$codeLenght = 4;

		return self::generateString($alphabet, $codeLenght);
	}

	public static function generateString($alphabet, $codeLenght) {
		$string = array();
		$alphaLength = strlen($alphabet) - 1;
		for ($i = 0; $i < $codeLenght; $i++) {
			$n = rand(0, $alphaLength);
			$string[] = $alphabet[$n];
		}
		return implode($string);
	}


	public static function getServerDateTime() {
		date_default_timezone_set('Asia/tehran');
		return date("Y-m-d H:i:s");
	}
}