# Installation: 

1- Composer require colbeh/auth

        
2- Add config file
        
        php artisan vendor:publish --provider="Colbeh\Auth\ServiceProvider" --tag=config

3- Go to config/auth_colbeh and modify that


# Upgrade:
        composer require colbeh/auth:x.x.x



# Guide


### Providers:
you can set multiple provider for different methods of login and signup
each provider contains:


    "providers"=>[
            ...
            
            "user"=>[
                "model"=>\App\Models\User::class,
                "type"=>"otp", // "otp" or "password"
                "need_validation"=>"1",  // 0 or 1 - determines that send sms or email for COL_PHONE_NUMBER_OR_EMAIL column
            ],
            
            ...
        ],

and call that

```php
$auth=new AuthController('user')
```

   note : by default provider uses 'user' and not need to determine that



### Validation
you can validate user by email or phone . 
1- Introduce your class of sms sender in auth_colbeh as SMSSenderClass key
note that SMSSenderClass class should extends from SmsSenderInterface interface
 
2- Introduce your class of email sender in auth_colbeh as EmailSenderClass key
note that EmailSenderClass class should extends from EmailSenderInterface interface

   note : this package just send email or sms and doesn't validate both of them
 
 
 
### Usage

#### Initialize : 
```php
$auth=new AuthController()
```

#### Login: 
login with username and password
```php
$auth->login($username, $password)
```

#### SignUp: 
sign up with username and password
```php
$auth->signUp($username, $password, $phoneOrEmail)
```

#### Validate: 
send validation email or sms for user
```php
$auth->validate($username, $code)
```

#### ResendCode: 
resend validation code
```php
$auth->resendCode($username)
```


#### ForgetPass:
 this method send an email or sms for recovery password
```php
$auth->forgetPass($username)
```


#### RecoveryPass: 
this method used for recovery (change) password after getting code from user
user can set his new password 
```php
$auth->recoveryPass($username,$code,$newPass)
```

if user not set password, system will create new password
```php
$newPass=$auth->recoveryPass($username,$code)
```

#### LoginOrSignUp: 
login or sign up user with phone number or email.In this method, system will send a validation code and you should use validate method to validate user 
```php
$auth->loginOrSignUp($phonenumberOrEmail)
```


