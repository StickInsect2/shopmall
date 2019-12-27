<?php


namespace app\api\service;


use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use app\api\service\Order as OrderService;
use app\api\model\Order as OrderModel;
use app\api\service\Token as TokenService;

class Pay {

    private $orderID;
    private $orderNO;

    function __construct($orderID) {
        if ($orderID) {
            throw new Exception('订单号暴怒允许为空');
        }
        $this->orderID = $orderID;
    }

    public function pay() {

        //订单号可能根本就不存在
        //订单号确实是存在的，但是，订单号和当前用户是不匹配的
        //订单有可能已经被支付过
        //订单库存量检测，调用order里面的库存量检测
        $this->checkOrderValid();
        $orderService = new OrderService();
        $status = $orderService->checkOrderStock($this->orderID);
        //如果库存量检测没有通过就直接返回订单终止支付
        if (!$status['pass']) {
            return $status;
        }

    }

    //库存量检测通过了，就生成微信服务器的预订单
    private function makeWxPreOrder() {
        
    }

    //检查各种可能存在的情况
    private function checkOrderValid() {
        $order = OrderModel::where('id', '=', $this->orderID)->find();
        //检测订单号可能根本就不存在的情况
        if (!$order) {
            throw new OrderException();
        }
        //检测订单号确实是存在的，但是，订单号和当前用户是不匹配的的情况
        if (!TokenService::isValidOperate($order->user_id)) {
            throw new TokenException([
                'msg' => '订单与用户不匹配',
                'errorCode' => 10003
            ]);
        }
        //检测订单有可能已经被支付过的情况
        if ($order->status != OrderStatusEnum::UNPAID) {
            throw new OrderException([
                'msg' => '订单已支付过啦',
                'errorCode' => 80003,
                'code' => 400
            ]);
        }
        $this->orderNo = $order->order_no;
        return true;
    }
}