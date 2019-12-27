<?php


namespace app\api\controller\v1;


use app\api\controller\BaseController;
use app\api\validate\IDMustBePositiveInt;

class Pay extends BaseController {

    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'getPreOrder']
    ];

    //预订单
    public function getPreOrder($id = '') {

        (new IDMustBePositiveInt())->goCheck();
    }
}