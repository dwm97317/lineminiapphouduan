<?php

namespace app\admin\controller\tools;

use app\admin\controller\Controller;
use app\common\model\UpdateLog;

/**
 * 更新日志
 * Class User
 * @package app\store\controller
 */
class Index extends Controller
{
    /**
     * 更新日志列表
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $updateLog = new updateLog;
        $list = $updateLog->getList();
        return $this->fetch('tools/updatelog', compact('list'));
    }
    
    /**
     * 添加更新日志
     * @return array|mixed
     */
    public function add()
    {
        $model = new updateLog;
        if (!$this->request->isAjax()) {
             return $this->fetch('tools/index/add', compact('list'));
        }
        // 新增记录
        $data = $this->postData('log');
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', url('tools.index/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    /**
     * 编辑更新日志
     * @return array|mixed
     */
    public function edit($log_id)
    {
   
        $model = updateLog::detail($log_id);
       
        if (!$this->request->isAjax()) {
             return $this->fetch('tools/index/edit', compact('model'));
        }
    
        // 新增记录
        $data = $this->postData('log');
        $data['update_time'] =time();
        if ($model->save($data)) {
            return $this->renderSuccess('修改成功', url('tools.index/index'));
        }
        return $this->renderError($model->getError() ?: '修改失败');
    }
    
    /**
     * 删除更新日志
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function delete($log_id){
      $model = updateLog::detail($log_id);
      if (!$model->delete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
}
