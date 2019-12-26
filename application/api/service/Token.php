<?php


namespace app\api\service;

use app\lib\exception\TokenException;
use think\Cache;
use think\Exception;
use think\Request;

class Token {

    //生成token字符串
    public static function generateToken() {
        //32个字符组成一组随机字符串
        //getRandChar是在common.php公共文件里面
        $randChars = getRandChar(32);
        //用三组字符串，进行md5加密
        $timestamp = $_SERVER['REQUEST_TIME_FLOAT'];
        //salt 盐
        $salt = config('secure.token_salt');
        return md5($randChars . $timestamp . $salt);
    }

//获取缓存里面的token变量
    public static function getCurrentTokenVar($key) {
        //token在用户传递过来的header里面
        $token = Request::instance()
            ->header('token');
        //从缓存里面取token
        $vars = Cache::get($token);
        if (!$vars) {
            throw new TokenException();
        } else {
            if (!is_array($vars)) {
                $vars = json_decode($vars, true);
            }
            if (array_key_exists($key, $vars)) {
                return $vars[$key];
            } else {
                throw new Exception('尝试获取的Token变量并不存在');
            }
        }
    }

    //获取当前用户的id号
    public static function getCurrentUid() {
        //token
        $uid = self::getCurrentTokenVar('uid');
        return $uid;
    }


}