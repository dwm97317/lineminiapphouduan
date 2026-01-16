<?php
namespace app\api\model;

use app\common\model\Category as CategoryModel;

/**
 * 商品分类模型
 * Class Category
 * @package app\common\model
 */
class Category extends CategoryModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
//        'create_time',
        'update_time'
    ];

    public static function getList() {

    }
    
    // 全部分类
    public function getCategoryAll(){
        return $this->field('category_id,name,parent_id')->select()->toArray();
    }
    
    // 获取热门全部分类
    public function gethotCategoryAll(){
        return $this->where('is_hot',1)->where('parent_id','>',0)->field('category_id,name,parent_id,is_hot')->select()->toArray();
    }
}
