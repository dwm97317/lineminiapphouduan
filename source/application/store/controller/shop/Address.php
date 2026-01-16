<?php

namespace app\store\controller\shop;

use app\store\controller\Controller;
use app\store\model\UserAddress;

/**
 * 代收点
 * Class Order
 * @package app\store\controller\shop
 */
class Address extends Controller
{
    /**
     * 代收点记录列表
     * @param int $shop_id
     * @param string $search
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new UserAddress;
        $list = $model->getDsList();
        return $this->fetch('index', compact('list'));
    }
    



    /**
     * 添加地址
     * @return array|bool|mixed
     * @throws \Exception
     */
    public function add()
    {
        $model = new UserAddress;
        if (!$this->request->isAjax()) {
            return $this->fetch('add');
        }
        // 新增记录
        $data = $this->postData('address');
        $data['address_type'] = 2;
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', url('shop.address/index'));
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 编辑会员地址
     * @param $address_id
     * @return array|bool|mixed
     * @throws \think\exception\DbException
     */
    public function edit($address_id)
    {
        // 会员地址详情
        $model = UserAddress::detail($address_id);
        // dump($model);die;
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model'));
        }
        // 新增记录
        if ($model->edit($this->postData('address'))) {
            return $this->renderSuccess('更新成功', url('shop.address/index'));
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 删除会员地址
     * @param $address_id
     * @return array
     * @throws \think\exception\DbException
     */
    public function delete($address_id)
    {
        // 地址详情
        $model = UserAddress::detail($address_id);
        if (!$model->remove()) {
            return $this->renderError($model->getError() ?: '删除失败');
        }
        return $this->renderSuccess('删除成功');
    }

}