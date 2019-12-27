<?php

namespace app\api\service;

use app\api\model\OrderProduct;
use app\api\model\Product;
use app\api\model\UserAddress;
use app\lib\exception\OrderException;
use app\lib\exception\UserException;
use app\api\model\Order as OrderModel;
use think\Db;
use think\Exception;

class Order {

    //订单的商品列表，也就是客户端传递过来的products参数
    protected $oProducts;

    //从数据库查出来的商品信息
    protected $products;

    protected $uid;

    //第一步
    public function place($uid, $oProducts) {
        //oProducts 和 products 做对比
        //products从数据库中查询出来
        $this->oProducts = $oProducts;
        $this->products = $this->getProductsByOrder($oProducts);
        $this->uid = $uid;
        //得到库存量检测的结果
        $status = $this->getOrderStatus();
        if (!$status['pass']) {
            $status['order_id'] = -1;
            return $status;
        }

        //第六步 开始创建订单
        //订单的快照
        $orderSnap = $this->snapOrder($status);
        $order = $this->createOrder($orderSnap);
        $order['pass'] = true;
        return $order;
    }

    //第九步 ，生成订单并存入数据库
    private function createOrder($snap) {
        Db::startTrans();
        try {
            //首先生成订单号
            $orderNo = $this->makeOrderNo();
            //实例化一个order模型对象并写入数据库
            $order = new OrderModel();
            $order->user_id = $this->uid;
            $order->order_no = $orderNo;
            $order->total_price = $snap['orderPrice'];
            $order->total_count = $snap['totalCount'];
            $order->snap_img = $snap['snapImg'];
            $order->snap_name = $snap['snapName'];
            $order->snap_address = $snap['snapAddress'];
            $order->snap_items = json_encode($snap['pStatus']);
            $order->save();

            $orderID = $order->id;
            $create_time = $order->create_time;
            foreach ($this->oProducts as &$p) {
                $p['order_id'] = $orderID;
            }
            $orderProduct = new OrderProduct();
            $orderProduct->saveAll($this->oProducts);
            Db::commit();
            return [
                'order_no' => $orderNo,
                'order_id' => $orderID,
                'create_time' => $create_time
            ];
        } catch (Exception $e) {
            Db::rollback();
            throw  $e;
        }
    }


    //第八步：用户的地址
    private function getUserAddress() {
        $userAddress = UserAddress::where('user_id', '=', $this->uid)
            ->find();
        if (!$userAddress) {
            throw new UserException(
                [
                    'msg' => '用户收货地址不存在，下单失败',
                    'errorCode' => 60001,
                ]);
        }
        return $userAddress->toArray();
    }


    //第七步， 生成订单快照
    private function snapOrder($status) {
        // status可以单独定义一个类
        $snap = [
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatus' => [],
            'snapAddress' => json_encode($this->getUserAddress()),
            'snapName' => $this->products[0]['name'],
            'snapImg' => $this->products[0]['main_img_url'],
        ];

        $snap['orderPrice'] = $status['orderPrice'];
        $snap['totalCount'] = $status['totalCount'];
        $snap['pStatus'] = $status['pStatusArray'];

        if (count($this->products) > 1) {
            $snap['snapName'] .= '等';
        }

        return $snap;
    }

    //第三步：库存量检测
    public function getOrderStatus() {
        $status = [
            'pass' => true,
            'orderPrice' => 0,
            'totalCount' => 0,
            'pStatusArray' => [],
        ];
        foreach ($this->oProducts as $oProduct) {
            $pStatus = $this->getproductStatus($oProduct['product_id'], $oProduct['count'], $this->products);
            if (!$pStatus['haveStock']) {
                $status['pass'] = false;
            }
            $status['orderPrice'] += $pStatus['totalPrice'];
            $status['totalCount'] += $pStatus['count'];
            array_push($status['pStatusArray'], $pStatus);
        }
        return $status;
    }

    //第四步：订单在数据库中的比较（传入订单id,要购买的订单的数量，以及订单列表）
    private function getproductStatus($oPID, $oCount, $products) {
        $pIndex = -1;
        $pStatus = [
            'id' => null,
            'haveStock' => false,
            'count' => 0,
            'name' => '',
            'totalPrice' => 0
        ];
        for ($i = 0; $i < count($products); $i++) {
            if ($oPID == $products[$i]['id']) {
                $pIndex = $i;
            }
        }
        if ($pIndex == -1) {
            throw new OrderException([
                'msg ' => 'id为' . $oPID . '商品不存在，创建订单不存在'
            ]);
        } else {
            $product = $products[$pIndex];
            $pStatus['id'] = $product['id'];
            $pStatus['count'] = $oCount;
            $pStatus['totalPrice'] = $product['price'] * $oCount;
            if ($product['stock'] - $oCount >= 0) {
                $pStatus['haveStock'] = true;
            }
            return $pStatus;
        }
    }


    //第二步：根据订单信息查找真实的商品信息
    private function getProductsByOrder($oProducts) {
//        foreach ($oProducts as $oProduct){
//            //循环的查询数据库会造成数据库的崩溃，因为你不确定订单列表中会有多少条数据
        $oPIDs = [];
        foreach ($oProducts as $item) {
            array_push($oPIDs, $item['product_id']);
        }
        $products = Product::all($oPIDs)
            ->visible(['id', 'price', 'stock', 'name', 'main_img_url'])
            ->toArray();
        return $products;
    }

    //生成订单号
    public static function makeOrderNo() {
        $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
        $orderSn =
            $yCode[intval(date('Y')) - 2017] . strtoupper(dechex(date('m'))) . date(
                'd') . substr(time(), -5) . substr(microtime(), 2, 5) . sprintf(
                '%02d', rand(0, 99));
        return $orderSn;
    }
}