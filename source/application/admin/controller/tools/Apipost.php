<?php

namespace app\admin\controller\tools;

use app\admin\controller\Controller;
use app\common\model\ApiPost as ApiPostModel;

/**
 * 更新日志
 * Class User
 * @package app\store\controller
 */
class Apipost extends Controller
{
    /**
     * 更新日志列表
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $ApiPostModel = new ApiPostModel;
        $list = $ApiPostModel->getList();
        return $this->fetch('tools/apipost', compact('list'));
    }
    
    /**
     * 添加更新日志
     * @return array|mixed
     */
    public function add()
    {
        $model = new ApiPostModel;
        if (!$this->request->isAjax()) {
             return $this->fetch('tools/apipost/add', compact('list'));
        }
        // 新增记录
        $data = $this->postData('api');
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', url('tools.apipost/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    /**
     * 编辑更新日志
     * @return array|mixed
     */
    public function edit($api_id)
    {
   
        $model = ApiPostModel::detail($api_id);
       
        if (!$this->request->isAjax()) {
             return $this->fetch('tools/apipost/edit', compact('model'));
        }
    
        // 新增记录
        $data = $this->postData('api');
        $data['update_time'] =time();
        if ($model->save($data)) {
            return $this->renderSuccess('修改成功', url('tools.apipost/index'));
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
    
    /**
     * 删除更新日志
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function delete($api_id){
      $model = ApiPostModel::detail($api_id);
      if (!$model->delete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
}
