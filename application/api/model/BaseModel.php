<?php


namespace app\api\model;


use think\Model;

class BaseModel extends Model {

    public function prefixImgUrl($value, $data) {
        $finalURL = $value;
        if ($data['from'] == 1) {
            $finalURL = config('setting.img_prefix') . $value;
        }
        return $finalURL;
    }
}