<?php


namespace app\api\validate;


class TokenGet extends BaseValidate {

    public $rule=[
        'code'=>'require|isNotEmpty'
    ];
     protected $message = [
         'code'=>'没有code，还想获取Token 做梦'
     ];

}