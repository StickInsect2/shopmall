<?php


namespace app\api\service;


use app\lib\enum\OrderStatusEnum;
use app\lib\exception\OrderException;
use app\lib\exception\TokenException;
use think\Exception;
use app\api\service\Order as OrderService;
use app\api\model\Order as OrderModel;
use app\api\service\Token as TokenService;
use think\Loader;

Loader::import('WxPay.WxPay', EXTEND_PATH, '.Api.php');

class Pay {

    private $orderID;
    private $orderNO;

    //第一步：必须要传入订单的id号
    function __construct($orderID) {
        if ($orderID) {
            throw new Exception('订单号不允许为空');
        }
        $this->orderID = $orderID;
    }


    //第二步：主函数
    public function pay() {

        //订单号可能根本就不存在
        //订单号确实是存在的，但是，订单号和当前用户是不匹配的
        //订单有可能已经被支付过
        //订单库存量检测，调用order里面的库存量检测
        $this->checkOrderValid();
        //第四步：检测库存量
        $orderService = new OrderService();
        $status = $orderService->checkOrderStock($this->orderID);
        //如果库存量检测没有通过就直接返回订单终止支付
        if (!$status['pass']) {
            return $status;
        }
        return $this->makeWxPreOrder($status['orderPrice']);
    }


    //第五步：库存量检测通过了，就生成微信服务器的预订单(构建微信支付订单信息)
    private function makeWxPreOrder($totalPrice) {
        $openid = Token::getCurrentTokenVar('openid');

        if (!$openid) {
            throw new TokenException();
        }
        //调用微信支付的api生成微信需要的预订单信息
        $wxOrderData = new \WxPayUnifiedOrder();
        $wxOrderData->SetOut_trade_no($this->orderNo);
        $wxOrderData->SetTrade_type('JSAPI');
        $wxOrderData->SetTotal_fee($totalPrice * 100);
        $wxOrderData->SetBody('零食商贩');
        $wxOrderData->SetOpenid($openid);
        $wxOrderData->SetNotify_url('微信返回结构的公网接收地址');

        return $this->getPaySignature($wxOrderData);
    }

    //第六步：向微信请求订单号并生成签名
    private function getPaySignature($wxOrderData) {
        //调用wxpayapi封装好的方法直接给微信发送请求
        $wxOrder = \WxPayApi::unifiedOrder($wxOrderData);

        // 失败时不会返回result_code
        if ($wxOrder['return_code'] != 'SUCCESS' || $wxOrder['result_code'] != 'SUCCESS') {
            Log::record($wxOrder, 'error');
            Log::record('获取预支付订单失败', 'error');
//            throw new Exception('获取预支付订单失败');
        }
        $this->recordPreOrder($wxOrder);
        $signature = $this->sign($wxOrder);
        return $signature;
    }

    //第七步：将微信返回给我们的prepay_id存到数据库中。
    private function recordPreOrder($wxOrder) {
        // 必须是update，每次用户取消支付后再次对同一订单支付，prepay_id是不同的
        OrderModel::where('id', '=', $this->orderID)
            ->update(['prepay_id' => $wxOrder['prepay_id']]);
    }

    // 第八步：签名
    private function sign($wxOrder) {
        $jsApiPayData = new \WxPayJsApiPay();
        $jsApiPayData->SetAppid(config('wx.app_id'));
        $jsApiPayData->SetTimeStamp((string)time());
        $rand = md5(time() . mt_rand(0, 1000));
        $jsApiPayData->SetNonceStr($rand);
        $jsApiPayData->SetPackage('prepay_id=' . $wxOrder['prepay_id']);
        $jsApiPayData->SetSignType('md5');
        $sign = $jsApiPayData->MakeSign();
        $rawValues = $jsApiPayData->GetValues();
        $rawValues['paySign'] = $sign;
        unset($rawValues['appId']);
        return $rawValues;
    }

    //第三步：检查各种可能存在的情况
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