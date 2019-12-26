<?php

namespace app\api\validate;

use app\lib\exception\ParameterException;
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


        if (!$this->check($params)) {

            $exception = new ParameterException([
                'msg' => $this->error
            ]);
            throw $exception;
        }
        return true;
    }

    protected function isPositiveInteger($value, $rule = '', $data = '', $field = '') {

        if (is_numeric($value) && is_int($value + 0) && ($value + 0) > 0) {
            return true;
        }
        return false;
    }

    public function isNotEmpty($value, $rule = '', $data = '', $field = '') {
        if (empty($value)) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * @param array $arrays 通常传入request.post变量数组
     * @return array 按照规则key过滤后的变量数组
     * @throws ParameterException
     */
    public function getDataByRule($arrays) {
        if (array_key_exists('user_id', $arrays) | array_key_exists('uid', $arrays)) {
            // 不允许包含user_id或者uid，防止恶意覆盖user_id外键
            throw new ParameterException([
                'msg' => '参数中包含有非法的参数名user_id或者uid'
            ]);
        }
        $newArray = [];
        foreach ($this->rule as $key => $value) {
            $newArray[$key] = $arrays[$key];
        }
        return $newArray;
    }

    //没有使用TP的正则验证，集中在一处方便以后修改
    //不推荐使用正则，因为复用性太差
    //手机号的验证规则
    protected function isMobile($value) {
        $rule = '^1(3|4|5|7|8)[0-9]\d{8}$^';
        $result = preg_match($rule, $value);
        if ($result) {
            return true;
        } else {
            return false;
        }
    }
}