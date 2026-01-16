<?php

namespace app\store\controller\data;

use app\store\controller\Controller;
use app\store\model\Countries as CountryModel;
class Country extends Controller
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
        $this->model = new CountryModel;
        $this->view->engine->layout(false);
    }

     public function countryList($title=''){
        $list = (new CountryModel())->getList($title);
       return $this->fetch('list', compact('list'));
    }

}