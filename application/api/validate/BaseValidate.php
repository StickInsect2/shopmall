<?php

namespace app\api\validate;

use app\lib\exception\BaseException;
use app\lib\exception\ParameterException;
use think\Exception;
use think\Request;
use think\Validate;

class BaseValidate extends Validate {

    /*
     * 获取http传入的参数
     * 对这些参数做验证
     * */
    public function goCheck() {

        $request = Request::instance();
        $params = $request->param();
        $result = $this->batch()->check($params);
        if (!$result) {

           $e = new ParameterException([
               'msg'=>''
           ]);
           throw $e;

        } else {
            return true;
        }
    }
}