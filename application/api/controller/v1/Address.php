<?php


namespace app\api\controller\v1;


use app\api\model\User as UserModel;
use app\api\validate\AddressNew;
use app\api\service\Token as TokenService;
use app\lib\exception\SuccessMessage;
use app\lib\exception\UserException;


class Address {

    public function createOrUpdateAddress() {

        $validate = new AddressNew();
        $validate->goCheck();
        //根据Token获取用户uid
        //根据uid来查找用户数据，判断用户是否存在，如果不存在抛出异常
        //获取从客户端提交来的地址信息，从而判断是添加地址还是更新地址

        $uid = TokenService::getCurrentUid();
        $user = UserModel::get($uid);
        if (!$user) {
            throw new UserException();
        }

        $dataArray = $validate->getDataByRule(input('post.'));


        $userAddress = $user->address;
        if (!$userAddress) {
            //通过模型的关联来新增一条记录
            $user->address()->save($dataArray);
        } else {
            // 更新
            $user->address->save($dataArray);
        }
//        return $user;
        //返回更新成功的提示信息
        return json(new SuccessMessage(),201);
    }
}