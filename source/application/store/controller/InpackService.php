<?php
namespace app\store\controller;

use app\store\model\InpackService as InpackServiceModel;

/**
 * 服务项目
 * Class Delivery
 * @package app\store\controller\setting
 */
class InpackService extends Controller
{



    /**
     * 删除模板
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete($id)
    {
        $model = new InpackServiceModel();
        if (!$model->deletes($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
    
    
    //新增服务项目
    public function add(){
        $param = \request()->param();
        $model = new InpackServiceModel();
        if(!$model->add($param)){
            return $this->renderError($model->getError() ?: '新增失败');
        }
        return $this->renderSuccess('新增成功');
    }
}
