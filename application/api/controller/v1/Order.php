<?php


namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\validate\OrderPlace;
use app\api\service\Token as TokenService;

class Order extends BaseController {

    //用户在选择商品后，向API提交包含它所选择商品的相关信息
    //API在接收到信息后，需要检查订单相关商品的库存量
    //有库存，把订单数据存入数据库中=下单成功了，返回客户端信息，告诉客户端可以支付了
    //调用我们的支付接口，进行支付
    //还需要再次进行库存量检测
    //服务器这边就可以调用微信的支付接口进行支付
    //微信会返回我们一个支付的结果(异步)
    //成功：也需要进行库存量的检测
    //成功：进行库存量的扣除  失败：返回一个支付失败的结果（无论成功）

    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'PlaceOrder']
    ];

    public function PlaceOrder() {
        (new OrderPlace())->goCheck();
        //通过input助手函数拿到客户端传递过来的参数，/a才可以拿到一个数组
        $products = input(post . products / a);
        $uid = TokenService::getCurrentUid();
    }

}