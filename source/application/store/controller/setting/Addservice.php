<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\LineService;


/**
 * 线路设置
 * Class Delivery
 * @package app\store\controller\setting
 */
class Addservice extends Controller
{


    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = (new LineService())->getList([]);
        return $this->fetch('index', compact('list'));
    }

    /**
     * 新增价格
     */
    public function add(){
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $model = new LineService();
        if ($model->add($this->postData('line'))) {
            return $this->renderSuccess('添加成功', url('setting.addservice/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }


    /**
     * 编辑打包服务
     */
    public function edit($id){
        // 模板详情
        $model = (new LineService())->details($id);
        $model['rule'] = json_decode($model['rule'],true);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('line'))) {
            return $this->renderSuccess('更新成功', url('setting.addservice/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

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
        $model = (new LineService())->deletes($id);
        if (!$model) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }
}
