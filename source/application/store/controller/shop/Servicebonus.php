<?php

namespace app\store\controller\shop;
use app\store\controller\Controller;
use app\store\model\store\shop\ShopBonus as ShopBonusModel;
use app\store\model\store\Shop as ShopModel;
/**
 * 仓库管理
 * Class Shop
 * @package app\store\controller\store
 */
class Servicebonus extends Controller
{
    /**
     * 仓库列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ShopBonusModel;
        $list = $model->getServiceList($this->request->get());
        // dump($list->toArray());die;
        $shopList = ShopModel::getAllList();
        return $this->fetch('index', compact('list','shopList'));
    }
    
    
    /**
     * 编辑仓库分红规则
     * @param $bonus_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($bonus_id)
    {
        // 仓库分红规则详情
        $model = ShopBonusModel::detail($bonus_id);
 
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('bonus'))) {
            return $this->renderSuccess('更新成功', url('shop.servicebonus/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除仓库
     * @param $shop_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($bonus_id)
    {
        // 仓库详情
        $model = ShopBonusModel::detail($bonus_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}