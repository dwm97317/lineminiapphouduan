<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\ExpressService as ExpressServiceModel;

/**
 * 快递标签配置控制器
 * Class ExpressService
 * @package app\store\controller\setting
 */
class ExpressService extends Controller
{
    /**
     * 标签列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ExpressServiceModel;
        $list = $model->getList($this->request->param());
        return $this->fetch('index', compact('list'));
    }

    /**
     * 添加标签
     * @return array|mixed
     */
    public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        $model = new ExpressServiceModel;
        if ($model->add($this->request->post())) {
            return $this->renderSuccess('添加成功', url('setting.express_service/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑标签
     * @param $service_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($service_id)
    {
        $model = ExpressServiceModel::detail($service_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        if ($model->edit($this->request->post())) {
            return $this->renderSuccess('更新成功', url('setting.express_service/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除标签
     * @param $service_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($service_id)
    {
        $model = ExpressServiceModel::detail($service_id);
        if ($model->remove()) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }
}
