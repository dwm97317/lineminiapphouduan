<?php

namespace app\store\controller\shop;
use app\store\controller\Controller;
use app\store\model\store\shop\ShopApply as ShopApplyModel;

/**
 * 仓库管理
 * Class Shop
 * @package app\store\controller\store
 */
class Apply extends Controller
{
    /**
     * 仓库列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ShopApplyModel;
        $list = $model->getList($this->request->get());
        return $this->fetch('index', compact('list'));
    }
    
    
    /**
     * 编辑仓库
     * @param $shop_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($shop_id)
    {
        // 仓库详情
        $model = ShopApplyModel::detail($shop_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('shop'))) {
            return $this->renderSuccess('更新成功', url('shop.apply/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    
    /**
     * 分销商审核
     * @param $apply_id
     * @return array|bool
     * @throws \think\exception\DbException
     */
    public function submit($id)
    {
        $model = ShopApplyModel::detail($id);
        if ($model->submit($this->postData('apply'))) {
            return $this->renderSuccess('操作成功');
        }
        return $this->renderError($model->getError() ?: '操作失败');
    }

    /**
     * 删除仓库
     * @param $shop_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($shop_id)
    {
        // 仓库详情
        $model = ShopApplyModel::detail($shop_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}