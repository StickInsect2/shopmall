<?php


namespace app\api\controller\v1;

use app\api\controller\BaseController;
use app\api\validate\OrderPlace;
use app\api\service\Token as TokenService;
use app\api\service\Order as OrderService;
use app\api\validate\PageParameter;
use app\api\model\Order as OrderModel;

class Order extends BaseController {

    //用户在选择商品后，向API提交包含它所选择商品的相关信息
    //API在接收到信息后，需要检查订单相关商品的库存量
    //有库存，把订单数据存入数据库中=下单成功了，返回客户端信息，告诉客户端可以支付了
    //调用我们的支付接口，进行支付
    //还需要再次进行库存量检测
    //服务器这边就可以调用微信的支付接口进行支付
    //小程序根据微信返回的结果拉起微信支付
    //微信会返回我们一个支付的结果(异步)
    //成功：也需要进行库存量的检测
    //成功：进行库存量的扣除  失败：返回一个支付失败的结果（无论成功）

    protected $beforeActionList = [
        'checkExclusiveScope' => ['only' => 'PlaceOrder'],
        'checkPrimaryScope' => ['only' => 'getSummaryByUser'],
    ];

    public function PlaceOrder() {
        (new OrderPlace())->goCheck();
        //通过input助手函数拿到客户端传递过来的参数，/a才可以拿到一个数组
        $products = input('post.products/a');
        $uid = TokenService::getCurrentUid();
        $order = new OrderService();
        $status = $order->place($uid, $products);
        return $status;
    }

    //分页
    public function getSummaryByUser($page = 1, $size = 15) {
        (new PageParameter())->goCheck();
        $uid = TokenService::getCurrentUid();
        $pagingOrders = OrderModel::getSummaryByUser($uid, $page, $size);
        if ($pagingOrders->isEmpty()) {
            return [
                'data' => [],
                'current_page' => $pagingOrders->currentPage()
            ];
        }
//        $collection = collection($pagingOrders->items());
//        $data = $collection->hidden(['snap_items', 'snap_address'])
//            ->toArray();
        $data = $pagingOrders->hidden(['snap_items', 'snap_address'])
            ->toArray();
        return [
            'current_page' => $pagingOrders->currentPage(),
            'data' => $data
        ];

    }


}