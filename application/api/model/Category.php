<?php


namespace app\api\model;


class Category extends BaseModel {

    protected $hidden = ['delete_time', 'update_time', 'create_time'];

    public function img() {
        return $this->belongsTo('Image', 'topic_img_id', 'id');
    }

    public static function getCategories($ids) {
        $categories = self::with('products')
            ->with('products.img')
            ->select($ids);
        return $categories;
    }

    public static function getCategory($id) {
        $category = self::with('products')
            ->with('products.img')
            ->find($id);
        return $category;
    }
}