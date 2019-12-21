<?php


namespace app\api\model;


use think\Model;

class Image  extends Model {
    protected $hidden = ['delete_time', 'id', 'from'];

    public function getUrlAttr($value,$data){
        $finalURL = $value;
        if($data['from'] == 1){
            $finalURL =config('setting.img_prefix').$value;
        }
        return $finalURL;
    }

}