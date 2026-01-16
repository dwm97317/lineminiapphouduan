<?php

namespace app\store\controller\shop;
use app\store\controller\Controller;
use app\store\model\store\shop\Capital as CapitalModel;
use app\store\model\store\Shop as ShopModel;
/**
 * 仓库管理
 * Class Shop
 * @package app\store\controller\store
 */
class Capital extends Controller
{
    /**
     * 仓库列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new CapitalModel;
        $map['shop_id'] = $this->store['user']['shop_id'];
        $list = $model->getList(array_merge($this->request->get(),$map));
        $shopList = ShopModel::getAllList();
   
        return $this->fetch('index', compact('list','shopList'));
    }
}