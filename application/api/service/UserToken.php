<?php


namespace app\api\service;


use app\lib\exception\TokenException;
use app\lib\exception\WeChatException;
use app\api\model\User as UserModel;

class UserToken extends Token {
    protected $code;
    protected $wxLoginUrl;
    protected $wxAppID;
    protected $wxAppSecret;

    function __construct($code) {
        $this->code = $code;
        $this->wxAppID = config('wx.app_id');
        $this->wxAppSecret = config('wx.app_secret');
        $this->wxLoginUrl = sprintf(
            config('wx.login_url'), $this->wxAppID, $this->wxAppSecret, $this->code);
    }


    public function get() {
        $result = curl_get($this->wxLoginUrl);

        // 注意json_decode的第二个参数true
        // 这将使字符串被转化为数组而非对象

        $wxResult = json_decode($result, true);

        if (empty($wxResult)) {
            throw new Exception('获取session_key及openID时异常，微信内部错误');
        } else {
            $loginFail = array_key_exists('errcode', $wxResult);
            echo '$loginFail这里';
            if ($loginFail) {
                $this->processLoginError($wxResult);
            } else {
                return $this->grantToken($wxResult);
            }
        }
    }


    private function grantToken($wxResult) {
        //拿到opinid,到数据库看一下这个openid是不是已经存在
        //如果已经存在，就不处理，如果不存在那么新增一条user记录。并返回uid,因为uid要存起来放到缓存里面
        //生成令牌，准备缓存数据，写入缓存
        //把令牌返回到客户端去
        //key:令牌
        //value:wxResult,uid,scope
        $openid = $wxResult['openid'];

        $user = UserModel::getByOpenID($openid);

        if ($user) {
            $uid = $user->id;
        } else {

            $uid = $this->newUser($openid);
        }
        $cachedValue = $this->prepareCachedValue($wxResult, $uid);
        $token = $this->saveToCache($cachedValue);
        return $token;

    }

    //写入缓存
    private function saveToCache($cachedValue) {
        //生成key(令牌就是一串随机的字符串)
        $key = self::generateToken();
        //因为$cachedValue是数组，就要把它转化为json格式的字符串作为键的值
        $value = json_encode($cachedValue);
        //令牌的过期时间就是缓存的过期时间
        $expire_in = config('setting.token_expire_in');

        //使用tp5自带的缓存(默认文件的缓存机制)
        $request = cache($key, $value, $expire_in);
        if(!$request){
            throw new TokenException([
                'msg'=>'服务器缓存异常',
                'errorCode'=>10005
            ]);
        }
        return $key;
    }

    //准备缓存数据
    private function prepareCachedValue($wxResult, $uid) {
        $cachedValue = $wxResult;
        $cachedValue['uid'] = $uid;
        $cachedValue['scope'] = 16;
        return $cachedValue;
    }

    //新增一条user记录
    private function newUser($openid) {
        $user = UserModel::create([
            'openid' => $openid
        ]);
    }

    // 获取微信服务器返回结果失败
    private function processLoginError($wxResult) {
        throw new WeChatException(
            [
                'msg' => $wxResult['errmsg'],
                'errorCode' => $wxResult['errcode']
            ]);
    }
}