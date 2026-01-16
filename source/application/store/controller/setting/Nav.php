<?php
namespace app\store\controller\setting;

use app\store\controller\Controller;
use app\store\model\WxappNavLink as WxappNavLinkModel;

/**
 * 商品分类
 * Class Category
 * @package app\store\controller\goods
 */
class Nav extends Controller
{
    /**
     * 商品分类列表
     * @return mixed
     */
    public function index()
    {
        $model = new WxappNavLinkModel;
        $list = $model->getList();
        // dump($list);die;
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除商品分类
     * @param $category_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($id)
    {
        $model = WxappNavLinkModel::get($id);
        if (!$model->remove($id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加导航
     * @return array|mixed
     */
    public function add()
    {
        $model = new WxappNavLinkModel;
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getList();
            return $this->fetch('add', compact('list'));
        }
        // 新增记录
        if ($model->add($this->postData('nav'))) {
            return $this->renderSuccess('添加成功', url('setting.nav/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑导航
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($id)
    {
        // 模板详情
        $model = WxappNavLinkModel::get($id, ['image']);
        if (!$this->request->isAjax()) {
            $list = $model->getList();
            return $this->fetch('edit', compact('model', 'list'));
        }
        // 更新记录
        if ($model->edit($this->postData('nav'))) {
            return $this->renderSuccess('更新成功', url('setting.nav/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
