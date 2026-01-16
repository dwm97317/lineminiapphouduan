<?php

namespace app\store\controller;

use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\ShopBonus as ShopBonusModel;
use app\store\model\Line;
use  app\store\model\PackageService;
/**
 * 仓库管理
 * Class Shop
 * @package app\store\controller\store
 */
class Shop extends Controller
{
    /**
     * 仓库列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new ShopModel;
        $line = (new Line())->getListAll();
        $service = (new PackageService())->getListAll();
        $map['shop_id'] = $this->store['user']['shop_id'];
        $list = $model->getList(array_merge($this->request->get(),$map));
        return $this->fetch('index', compact('list','line','service'));
    }
    

    /**
     * 腾讯地图坐标选取器
     * @return mixed
     */
    public function getpoint()
    {
        $this->view->engine->layout(false);
        return $this->fetch('getpoint');
    }
    
    /**
     * 设置仓库分成规则
     * @return mixed
     */
    public function discount()
    {
        $data = $this->request->post();
        $model = new ShopBonusModel;
        $result = $data['bonus'];
        $result['shop_id'] = $data['shop_id'];
        if($model->add($result)){
             return $this->renderSuccess('添加成功', url('shop.bonus/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    /**
     * 设置服务项目分成规则
     * @return mixed
     */
    public function servicediscount()
    {
        $data = $this->request->post();
        $model = new ShopBonusModel;
        $result = $data['bonus'];
        $result['shop_id'] = $data['shop_id'];
        $result['source'] = 20;
        if($model->add($result)){
             return $this->renderSuccess('添加成功', url('shop.bonus/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }
    
    

    /**
     * 添加仓库
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new ShopModel;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        if ($model->add($this->postData('shop'))) {
            return $this->renderSuccess('添加成功', url('shop/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑仓库
     * @param $shop_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($shop_id)
    {
        // 仓库详情
        $model = ShopModel::detail($shop_id);
        // dump($model->toArray());die;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('shop'))) {
            return $this->renderSuccess('更新成功', url('shop/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除仓库
     * @param $shop_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($shop_id)
    {
        // 仓库详情
        $model = ShopModel::detail($shop_id);
        if (!$model->setDelete()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}