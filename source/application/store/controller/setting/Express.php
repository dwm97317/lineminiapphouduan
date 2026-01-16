<?php

namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Express as ExpressModel;

/**
 * 物流公司
 * Class Express
 * @package app\store\controller\setting
 */
class Express extends Controller
{
    /**
     * 物流公司列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ExpressModel;
        $list = $model->getList();
        return $this->fetch('index', compact('list'));
    }
    
        
    /**
     * 复用国家
     */
    public function copy(){
        $model = (new ExpressModel());
        $data = $model->select();
        if(count($data)>0){
           return $this->renderError($model->getError() ?: '请先删除现有的物流公司再复用'); 
        }
        if ($model->copy()) {
            return $this->renderSuccess('复用成功');
        }
        return $this->renderError($model->getError() ?: '复用失败');
    }


    /**
     * 删除物流公司
     * @param $express_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($express_id)
    {
        $model = ExpressModel::detail($express_id);
        if (!$model->remove()) {
            $error = $model->getError() ?: '删除失败';
            return $this->renderError($error);
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加物流公司
     * @return array|mixed
     */
    public function add()
    {
        $track = getFileData('assets/track.json');
        // $track = array_slice($track,0,20000,true);
        if (!$this->request->isAjax()) {
            return $this->fetch('add',compact('track'));
        }
        // 新增记录
        $model = new ExpressModel;
        if ($model->add($this->postData('express'))) {
            return $this->renderSuccess('添加成功', url('setting.express/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑物流公司
     * @param $express_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($express_id)
    {
        // 模板详情
        $track = getFileData('assets/track.json');
        // $track = array_slice($track,0,20000,true);
        $model = ExpressModel::detail($express_id);
        // dump($model);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model','track'));
        }
        // 更新记录
        if ($model->edit($this->postData('express'))) {
            return $this->renderSuccess('更新成功', url('setting.express/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 物流公司编码表
     * @return mixed
     */
    public function company()
    {
        $track = getFileData('assets/track.json');
        return $this->fetch('company',compact('track'));
    }

}