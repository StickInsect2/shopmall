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
               'msg'=>$this->error
           ]);
           throw $e;

        } else {
            return true;
        }
    }

    protected function isPositiveInteger($value, $rule='', $data='', $field='')
    {
        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return false;
    }
}