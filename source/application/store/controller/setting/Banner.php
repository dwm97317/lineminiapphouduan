<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\Banner as BannerModel;
use app\store\model\UploadFile;

/**
 * 线路设置
 * Class Delivery
 * @package app\store\controller\setting
 */
class Banner extends Controller
{
    /**
     * 线路列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new BannerModel();
        $query = $this->getData();
        $list = $model->getList($query);
        if (!$list->isEmpty()){
            $list = dataMapRender($list,'redirect_type',[
                1 => '小程序内部链接',
                2 => '外部链接'
            ]);
        }
        // dump($list->toArray());die;
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除模板
     * @param $delivery_id
     * @return array
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function delete()
    {
        $id = $this->request->param('id');

        $model = (new BannerModel())->details($id);
        if (!$model->remove()) {
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
    public function add()
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
    }

    /**
     * 编辑配送模板
     * @param $delivery_id
     * @return array|mixed
     * @throws \think\Exception
     * @throws \think\exception\DbException
     * @throws \think\exception\PDOException
     */
    public function edit($id)
    {
        // 模板详情
        $model = (new BannerModel())->details($id);
        if ($model['image_id']){
            $model['file'] = (new UploadFile())->find($model['image_id']);
        }
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 更新记录
        $data = $this->postData('banner');
        $data['id'] = $id;
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', url('setting.banner/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
