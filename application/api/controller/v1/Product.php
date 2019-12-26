<?php


namespace app\api\controller\v1;


use app\api\validate\Count;
use app\api\model\Product as ProductModel;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\ProductException;

class Product {

    //最近新品
    public function getRecent($count = 15) {
        (new Count())->goCheck();
        $result = ProductModel::getMostRecent($count);

        if ($result->isEmpty()) {
            throw  new ProductException();
        }
        $result = $result->hidden(['summary']);
        return $result;
    }

    //获取一级分类下的所有商品
    public function getAllInCategory($id) {
        (new IDMustBePositiveInt())->goCheck();
        $products = ProductModel::getProductsByCategory($id);
        if ($products->isEmpty()) {
            throw new ProductException();
        }
        $products = $products->hidden(['summary']);
        return $products;
    }

    //获取商品详情
    public function getProductDetail($id) {
        (new IDMustBePositiveInt())->goCheck();
        $productDetail = ProductModel::getProductDetail($id);
        if (!$productDetail) {
            throw new ProductException();
        }
        return $productDetail;
    }

    public function deleteOne($id){

    }
}