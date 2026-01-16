<?php

namespace app\api\controller;

use app\api\model\Category as CategoryModel;
use app\api\model\WxappCategory as WxappCategoryModel;

/**
 * 商品分类控制器
 * Class Goods
 * @package app\api\controller
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

    /**
     * 获取分类列表（用于包裹领取页面）
     * @return array
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        // 获取所有分类（带图片关联）
        $list = CategoryModel::with(['image'])
            ->order(['sort' => 'asc', 'create_time' => 'asc'])
            ->select();
        
        return $this->renderSuccess(['data' => $list]);
    }

}
