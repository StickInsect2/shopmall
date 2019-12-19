<?php


namespace app\api\model;


use think\Exception;

class Banner {
    public static function  getBannerByID($id){
        //根据id获取banner信息
        try{
            1/0;
        }catch (Exception $ex){
         //TODO: 可以记录日志
            throw $ex;
        }
        return "this is banner info";
    }
}