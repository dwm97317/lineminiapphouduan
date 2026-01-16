<?php
namespace app\web\controller;

use app\web\model\Category as CategoryModel;
use app\web\model\WxappCategory as WxappCategoryModel;

/**
 * 商品分类控制器
 * Class Goods
 * @package app\web\controller
 */
class Category extends Controller
{
    /**
     * 分类页面
     * @return array
     * @throws \think\exception\DbException
     */
    public function index()
    {
        // 分类模板
        $templet = WxappCategoryModel::detail();
        // 商品分类列表
        $list = array_values(CategoryModel::getCacheTree());
        return $this->renderSuccess(compact('templet', 'list'));
    }

}
