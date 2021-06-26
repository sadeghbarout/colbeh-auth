<?php

namespace Colbeh\Auth\Controllers;

use Colbeh\Auth\Col;
use Colbeh\Auth\Config;
use Colbeh\Auth\Exceptions\AlreadyValidatedException;
use Colbeh\Auth\Exceptions\InvalidOtpException;
use Colbeh\Auth\Exceptions\OtpExpiredException;
use Colbeh\Auth\Exceptions\TokenAlreadySentException;
use Colbeh\Auth\Exceptions\TooManyTriesException;
use Colbeh\Auth\Exceptions\UserExistsException;
use Colbeh\Auth\Exceptions\UserIncorrectPasswordException;
use Colbeh\Auth\Exceptions\UserNotExistsException;
use Colbeh\Auth\Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Hash;

class AuthController {

	protected $provider;

	function __construct($provider = null) {
		if($provider == null){
			$this->provider = Config::providers()["user"];
		}else{
			$this->provider = Config::providers()[$provider];
		}
	}

//------------------------------------------------------------------------------------

	/**
	 * @param $username
	 * @param $password
	 *
	 * @return string
	 */
	public function login($username, $password) {

		$user=$this->getUser($username);

		$response = $this->loginUser($user, $password);

		return $response;
	}


	/**
	 * @param $user
	 * @param $password
	 *
	 * @return Model | string
	 * @throws UserIncorrectPasswordException
	 */
	private function loginUser($user, $password) {

		if(!Hash::check($password,$user[Col::password()])){
			throw new UserIncorrectPasswordException();
		}

		if ($user[Col::validationStatus()]!=Col::enumValidated()) {
			$this->sendValidation1($user);
			return 'validation'; // todo : return better response
		}

		return $user;

	}


	/**
	 * @param Model $user
	 *
	 * @return  Model
	 */
	private function sendValidation1($user) {
		$otpCode = Helper::generateOtpCode();
		$user[Col::otp()] = $otpCode;
		$user[Col::otpTryCount()]=0;
		$user[Col::otpLastDate()] = Helper::getServerDateTime();
		$user->save();

		$phoneOrEmail = $user[Col::phoneNumberOrEmail()];
		Helper::getSender($phoneOrEmail)::sendValidation($phoneOrEmail, $otpCode);
		return $user;
	}

//------------------------------------------------------------------------------------

	/**
	 * @param      $username
	 * @param      $password
	 * @param null $phoneOrEmail
	 *
	 * @return Model
	 * @throws UserExistsException
	 */
	public function signUp($username, $password, $phoneOrEmail = null) {

		if ($this->getUser($username,false)) {
			throw new UserExistsException();
		}

		$user = $this->createUser($username, $password, $phoneOrEmail);

		return $user;
	}


	/**
	 * @param $username
	 * @param $password
	 * @param $phoneOrEmail
	 *
	 * @return Model
	 */
	private function createUser($username, $password, $phoneOrEmail) {
		$model = $this->getModel();
		$user = new $model;
		$user[Col::username()] = $username;
		$user[Col::password()] = bcrypt($password);
		$user->save();

		if (Config::needValidation($this->provider) == '1') {
			$user = $this->sendValidation($user, $phoneOrEmail);
		}

		return $user;
	}

	/**
	 * @param Model $user
	 * @param $phoneOrEmail
	 *
	 * @return Model
	 */
	private function sendValidation($user, $phoneOrEmail) {
		$otpCode = Helper::generateOtpCode();
		$user[Col::phoneNumberOrEmail()] = $phoneOrEmail;
		$user[Col::otp()] = $otpCode;
		$user[Col::otpTryCount()] = 0;
		$user[Col::otpLastDate()] = Helper::getServerDateTime();
		$user[Col::validationStatus()] = Col::enumNotValidated();
		$user->save();

		Helper::getSender($phoneOrEmail)::sendValidation($phoneOrEmail, $otpCode);
		return $user;
	}



//------------------------------------------------------------------------------------

	/**
	 * @param $username
	 * @param $code
	 *
	 * @return Model
	 */
	public function validate($username, $code) {

		$user=$this->getUser($username);

		$user=$this->checkValidationCode($user, $code);

		return $user;
	}


	/**
	 * @param $user
	 * @param $code
	 *
	 * @return Model
	 * @throws AlreadyValidatedException
	 */
	private function checkValidationCode($user, $code) {
		if($user[Col::validationStatus()]==Col::enumValidated()  &&  Config::type($this->provider) == "password")
			throw new AlreadyValidatedException();

		$user=$this->checkCode($user, $code);

		$user[Col::validationStatus()]=Col::enumValidated();
		$user->save();

		return $user;

	}

	/**
	 * @param Model $user
	 * @param $code
	 *
	 * @return Model
	 * @throws InvalidOtpException
	 * @throws OtpExpiredException
	 * @throws TooManyTriesException
	 */
	private function checkCode($user, $code) {
		$diff=strtotime(Helper::getServerDateTime())-strtotime($user[Col::otpLastDate()]);

		if($diff>Config::otpLifeTime())
			throw new OtpExpiredException();

		if($user[Col::otpTryCount()]>=Config::tryCount())
			throw new TooManyTriesException();

		if($user[Col::otp()]!=$code){
			$user[Col::otpTryCount()]+=1;
			$user->save();
			throw new InvalidOtpException();
		}


		$user[Col::otp()]='';
		$user->save();

		return $user;
	}



//------------------------------------------------------------------------------------

	/**
	 * @param $username
	 *
	 * @return Model
	 */
	public function resendCode($username) {

		$user=$this->getUser($username);

		$user=$this->doResendCode($user);

		return $user;
	}


	/**
	 * @param Model $user
	 *
	 * @return Model
	 * @throws TokenAlreadySentException
	 */
	private function doResendCode($user) {
		$diff=strtotime(Helper::getServerDateTime())-strtotime($user[Col::otpLastDate()]);

		if($diff<Config::otpLifeTime())
			throw new TokenAlreadySentException();


		$otpCode = Helper::generateOtpCode();
		$user[Col::otp()] = $otpCode;
		$user[Col::otpTryCount()] = 0;
		$user[Col::otpLastDate()] = Helper::getServerDateTime();
		$user->save();

		$phoneOrEmail=$user[Col::phoneNumberOrEmail()];
		Helper::getSender($phoneOrEmail)::sendValidation($phoneOrEmail, $otpCode);
		return $user;
	}

//------------------------------------------------------------------------------------

	/**
	 * @param $username
	 *
	 * @return Model
	 */
	public function forgetPass($username) {

		$user=$this->getUser($username);

		$user=$this->doForgetPassword($user);

		return $user;
	}

	/**
	 * @param Model $user
	 *
	 * @return Model
	 * @throws TokenAlreadySentException
	 */
	private function doForgetPassword($user) {
		$diff=strtotime(Helper::getServerDateTime())-strtotime($user[Col::otpLastDate()]);
		if($diff<Config::otpLifeTime())
			throw new TokenAlreadySentException();


		$otpCode = Helper::generateOtpCode();
		$user[Col::otp()] = $otpCode;
		$user[Col::otpTryCount()] = 0;
		$user[Col::otpLastDate()] = Helper::getServerDateTime();
		$user->save();

		$phoneOrEmail=$user[Col::phoneNumberOrEmail()];
		Helper::getSender($phoneOrEmail)::sendForgetPass($phoneOrEmail, $otpCode);
		return $user;
	}
//------------------------------------------------------------------------------------

	/**
	 * @param      $username
	 * @param      $code
	 * @param null $newPass
	 *
	 * @return string
	 */
	public function recoveryPass($username,$code,$newPass=null) {

		$user=$this->getUser($username);

		$newPass=$this->doRecoveryPassword($user,$code,$newPass);

		return $newPass;
	}

	/**
	 * @param Model $user
	 * @param $code
	 * @param $newPass
	 *
	 * @return string
	 */
	private function doRecoveryPassword($user,$code,$newPass) {

		$user=$this->checkCode($user,$code);

		if($newPass==null){
			$newPass=Helper::generatePassword();
		}
		$user[Col::password()] = bcrypt($newPass);
		$user->save();

		return $newPass;
	}
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------


	/**
	 * @param $phonenumberOrEmail
	 *
	 * @return Model
	 */
	public function loginOrSignUp($phonenumberOrEmail) {

		$user=$this->getUser($phonenumberOrEmail,false);
		if ($user==null) {
			$user = $this->SignUpUserWithPhone($phonenumberOrEmail);
		}else{
			$user= $this->sendValidation2($user);
		}

		return $user;
	}

	/**
	 * @param $phonenumberOrEmail
	 *
	 * @return Model
	 */
	private function SignUpUserWithPhone($phonenumberOrEmail) {
		$model = $this->getModel();
		$user = new $model;
		$user[Col::phoneNumberOrEmail()] = $phonenumberOrEmail;
		$user->save();

		$user = $this->sendValidation2($user);

		return $user;
	}

	/**
	 * @param Model $user
	 *
	 * @return Model
	 */
	private function sendValidation2($user) {
		$otpCode = Helper::generateOtpCode();
		$user[Col::otp()] = $otpCode;
		$user[Col::otpTryCount()]=0;
		$user[Col::otpLastDate()] = Helper::getServerDateTime();
		$user->save();

		$phoneOrEmail = $user[Col::phoneNumberOrEmail()];
		Helper::getSender($phoneOrEmail)::sendValidation($phoneOrEmail, $otpCode);
		return $user;
	}







//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------
//------------------------------------------------------------------------------------

	/**
	 * @param      $username
	 * @param bool $checkExists
	 *
	 * @return Model
	 * @throws UserNotExistsException
	 */
	private function getUser($username,$checkExists=true) {
		$model = $this->getModel();

		if(Config::type($this->provider) == "otp")
			$user = $model->where(Col::phoneNumberOrEmail(), $username)->first();
		else
			$user = $model->where(Col::username(), $username)->first();

		
		if( $checkExists && $user==null)
			throw new UserNotExistsException();
		
		return $user;
	}

	/**
	 * @return Model
	 */
	private function getModel(): Model {
		$modelStr = Config::model($this->provider);
		$model = new $modelStr;
		return $model;
	}




}



