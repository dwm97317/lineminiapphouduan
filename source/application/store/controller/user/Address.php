<?php

namespace app\store\controller\user;
use app\common\model\Setting;
use app\store\controller\Controller;
use app\store\model\UserAddress;
use app\store\model\Countries;
/**
 * 用户地址
 * Class UserAddress
 * @package app\store\controller\user
 */
class Address extends Controller
{
    /**
     * 用户地址列表
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $model = new UserAddress;
        $list = $model->getList();
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
        $Countries = new Countries();
        $countryList = $Countries->getListAll();
        $set = Setting::detail('store')['values']['address_setting'];
        if (!$this->request->isAjax()) {
            return $this->fetch('add',compact('set','countryList'));
        }
        // 新增记录
        $data = $this->postData('address');
        $country = $Countries->details($data['country_id']);
        $data['country'] = $country['title'];
        if ($model->add($data)) {
            return $this->renderSuccess('添加成功', url('user/address'));
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
        $Countries = new Countries();
        $set = Setting::detail('store')['values']['address_setting'];
        $countryList = (new Countries())->getListAll();
        $model = UserAddress::detail($address_id);
        if (!$this->request->isAjax()) {
            return $this->fetch('edit', compact('model','set','countryList'));
        }
        // 新增记录
        $data = $this->postData('address');
        $country = $Countries->details($data['country_id']);
        $data['country'] = $country['title'];     
        if ($model->edit($data)) {
            return $this->renderSuccess('更新成功', url('user/address'));
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