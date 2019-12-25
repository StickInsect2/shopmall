<?php


namespace app\api\controller\v1;


use app\api\service\UserToken;
use app\api\validate\TokenGet;

class Token {
    /**
     * 用户获取令牌（登陆）
     * @url /token/user
     * @POST code
     */
    public function getToken($code='') {
        (new TokenGet())->goCheck();
        $wx = new UserToken($code);
        $token = $wx->get();
        return [
            'token' => $token
        ];

    }
}