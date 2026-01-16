<?php

namespace app\store\controller\goods;

use app\store\controller\Controller;
use app\store\model\Category as CategoryModel;
use app\store\model\Line;
/**
 * 商品分类
 * Class Category
 * @package app\store\controller\goods
 */
class Category extends Controller
{
    /**
     * 商品分类列表
     * @return mixed
     */
    public function index()
    {
        $model = new CategoryModel;
        $list = $model->getCacheTree();
        return $this->fetch('index', compact('list'));
    }

    /**
     * 删除商品分类
     * @param $category_id
     * @return array|bool
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    public function delete($category_id)
    {
        $model = CategoryModel::get($category_id);
        $res = (new Line())->where('FIND_IN_SET(:ids,categorys)', ['ids' => $category_id])->find();
        if($res){
           return $this->renderError($model->getError() ?: '该类目被【'.$res['name'].'】使用中，请先在集运路线中取消该类目'); 
        }
        $pacategory = (new CategoryModel())->where("parent_id",$category_id)->select();
        if($model['parent_id']==0 && count($pacategory)>0){
            return $this->renderError($model->getError() ?: '该类目有子类目，请先删除子类目'); 
        }
       
        if (!$model->remove($category_id)) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

    /**
     * 添加商品分类
     * @return array|mixed
     */
    public function add()
    {
        $model = new CategoryModel;
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getCacheTree();
            return $this->fetch('add', compact('list'));
        }
        // 新增记录
        if ($model->add($this->postData('category'))) {
            return $this->renderSuccess('添加成功', url('goods.category/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑商品分类
     * @param $category_id
     * @return array|mixed
     * @throws \think\exception\DbException
     */
    public function edit($category_id)
    {
        // 模板详情
        $model = CategoryModel::get($category_id, ['image']);
        if (!$this->request->isAjax()) {
            // 获取所有地区
            $list = $model->getCacheTree();
            return $this->fetch('edit', compact('model', 'list'));
        }
        // 更新记录
        if ($model->edit($this->postData('category'))) {
            return $this->renderSuccess('更新成功', url('goods.category/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

}
