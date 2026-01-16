<?php

namespace app\store\controller\data;

use app\store\controller\Controller;
use app\store\model\Category as CategoryModel;
class Category extends Controller
{
    private $model;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new CategoryModel;
        $this->view->engine->layout(false);
    }

     public function categoryList($title=''){
        $list = (new CategoryModel())->getListChild($title);

       return $this->fetch('list', compact('list'));
    }

}