<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\PackageService;


/**
 * 线路设置
 * Class Delivery
 * @package app\store\controller\setting
 */
class Package extends Controller
{


    /**
     * 用户列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index(){
        $list = (new PackageService())->getList([]);
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
        $model = new PackageService();
        if ($model->add($this->postData('package'))) {
            return $this->renderSuccess('添加成功', url('setting.package/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }


    /**
     * 编辑打包服务
     */
    public function edit($id){
        // 模板详情
        $model = (new PackageService())->details($id);
        // dump($model);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('package'))) {
            return $this->renderSuccess('更新成功', url('setting.package/index'));
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
        $model = (new PackageService())->deletes($id);
        if (!$model) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加配送模板
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
  /*  public function add()
    {
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $model = new BannerModel();
        if ($model->add($this->postData('banner'))) {
            return $this->renderSuccess('添加成功', url('setting.banner/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }*/

    /**
     * 编辑配送模板
     * @param $delivery_id
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
  /*  public function edit($id)
    {
        // 模板详情
        $model = (new BannerModel())->details($id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        if ($model->edit($this->postData('line'))) {
            return $this->renderSuccess('更新成功', url('setting.banner/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }*/

}
