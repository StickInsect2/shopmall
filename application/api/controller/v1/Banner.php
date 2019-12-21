<?php

namespace app\api\controller\v1;
use app\api\model\Banner as BannerModel;
use app\api\validate\IDMustBePositiveInt;
use app\lib\exception\BannerMissException;

class Banner{

    public function getBanner($id) {

        (new IDMustBePositiveInt())->goCheck();

        $banner = BannerModel::getBannerByID($id);

        if (!$banner) {
            throw new BannerMissException();
        }
        return $banner;
    }

}