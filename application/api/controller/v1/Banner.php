<?php


namespace app\api\controller\v1;

use app\api\validate\IDMustBePositiveInt;

class Banner {

    /*
     * 获取指定id 的banner信息
     * @url  /banner/:id
     * @http GET
     * @id banner的id
     * */

    public function getBanner($id) {

        (new IDMustBePositiveInt())->goCheck();
    }

}