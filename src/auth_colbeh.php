<?php

return [


	/*
	 * Model for Auth
	 */


	"providers"=>[
		"user"=>[
			"model"=>\App\Models\User::class,
			"type"=>"otp",
			"need_validation"=>"1",
		],
		"admin"=>[
			"model"=>\App\Models\Admin::class,
			"type"=>"password",
			"need_validation"=>"0",
		],
	],


	/*
	 * Email and SMS sender
	 */
	"SMSSenderClass"=>'',
	"EmailSenderClass"=>'',


	/*
	 * DB Columns
	 */
	"COL_USERNAME"=>env('COL_USERNAME',''),
	"COL_PASSWORD"=>env('COL_PASSWORD',''),
	"COL_PHONE_NUMBER_OR_EMAIL"=>env('COL_PHONE_NUMBER_OR_EMAIL',''),
	"COL_OTP"=>env('COL_OTP',''),
	"COL_OTP_TRY_COUNT"=>env('COL_OTP_TRY_COUNT',''),
	"COL_OTP_LAST_DATE"=>env('COL_OTP_LAST_DATE',''),
	"COL_VALIDATION_STATUS"=>env('COL_VALIDATION_STATUS',''), // should have enum not_validated,validated


	/*
	 * configs
	 */
	"config_try_count"=>env('config_try_count',''),
	"config_otp_life_time"=>env('config_otp_life_time',''),
//	"config_need_validation"=>env('config_need_validation2',''), // validate type (phone / email)
//	"config_need_validation"=>env('config_need_validation2',''), // validate inputs





];