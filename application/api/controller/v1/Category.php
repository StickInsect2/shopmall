<?php


namespace app\api\controller\v1;
use app\api\controller\BaseController;
use app\api\model\Category as CategoryModel;
use app\lib\exception\CategoryException;

class Category extends BaseController {

    //获取所有的一级分类
    public function getAllCategories(){
        $result = CategoryModel::all([],'img');
        if(empty($result)){
            throw new CategoryException();
        }
        return $result;
    }
}