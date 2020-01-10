<?php


namespace app\api\model;


class Banner extends BaseModel {
    //模型中的对数据表的缓存：php think optimize:schema

    public function items(){
        return $this->hasMany('BannerItem','banner_id','id');
    }

    public static function getBannerByID($id){
        $banner = self::with(['items','items.img'])->find($id);
        return $banner;
    }
}