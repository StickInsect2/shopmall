<?php


namespace app\api\controller\v1;

use app\api\model\Theme as ThemeModel;
use app\api\validate\IDCollection;
use app\api\validate\IDMustBePositiveInt;
use app\api\validate\ThemeProduct;
use app\lib\exception\SuccessMessage;
use app\lib\exception\ThemeException;
use think\Controller;

class Theme extends Controller {

    /*
     * @utl /theme?ids = id1,id2,id3
     * @return 一组theme 模型
     */
    public function getSimpleList($ids = '') {
        (new IDCollection())->goCheck();
        $ids = explode(',', $ids);
        $result = ThemeModel::with('topicImg,headImg')->select($ids);
        if ($result->isEmpty()) {
            throw new ThemeException();
        }
        return $result;
    }

    /*
     * @url /theme/:id
     */

    public function getComplexOne($id) {
        (new IDMustBePositiveInt())->goCheck();
        $result = ThemeModel::getThemeWithProducts($id);
        if (!$result) {
            throw new ThemeException();
        }
        return $result;
    }

}