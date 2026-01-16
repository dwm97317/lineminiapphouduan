<?php
namespace app\web\controller;

use app\web\model\UserAddress;
use app\web\model\Country;
use app\web\model\Setting as SettingModel;
/**
 * 收货地址管理
 * Class Address
 * @package app\web\controller
 */
class Address extends Controller
{
    
     public function index(){
          dump(input());die;
         $list = $this->lists();
        
         return $this->fetch('usercenter/address',compact()('list'));
    }
    /**
     * 收货地址列表
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function lists()
    {
        $user = $this->User();
        $model = new UserAddress;
        $keyword = $this->request->param('keyword');
        $list = $model->getList($user['user_id'],$keyword);
        return $this->renderSuccess([
            'list' => $list,
            'default_id' => $user['address_id'],
        ]);
    }

    /**
     * 添加收货地址
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function add()
    {
        $model = new UserAddress;
        if ($model->add($this->getUser(), $this->request->post())) {
            return $this->renderSuccess('添加成功');
        }
        return $this->renderError($model->getError() ?: '添加失败');
    }

    /**
     * 收货地址详情
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function detail($address_id)
    {
        $user = $this->getUser();
        $detail = UserAddress::detail($user['user_id'], $address_id);
       
        $detail['is_default'] = $user['address_id']==$detail['address_id']?1:0;
        return $this->renderSuccess(compact('detail'));
    }

    /**
     * 编辑收货地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function edit($address_id)
    {
             
        $user = $this->user;
        $Countries = (new Country());
        $setting = SettingModel::getItem('store');
        $countryList = $Countries->getListAll();
        $model = UserAddress::detail($user['user']['user_id'], $address_id);
      
        if (!$this->request->isAjax()) {
            return $this->fetch('package/editaddress', compact('model','countryList','setting')); 
        }
        //   dump($this->request->post()['formJson']);die;
        if ($model->edit($this->request->post()['formJson'])) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }
    
    
    /**
     * 编辑收货地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function saveedit($address_id)
    {
        $model = new UserAddress;
        dump($address_id);die;
        if ($model->edit($this->request->post()['address'])) {
            return $this->renderSuccess('更新成功');
        }
        return $this->renderError($model->getError() ?: '更新失败');
    }

    /**
     * 设为默认地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function setDefault($address_id)
    {
        $user = $this->getUser();
        $model = UserAddress::detail($user['user_id'], $address_id);
        if ($model->setDefault($user)) {
            return $this->renderSuccess([], '设置成功');
        }
        return $this->renderError($model->getError() ?: '设置失败');
    }

    /**
     * 删除收货地址
     * @param $address_id
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public function delete($address_id)
    {
        $user = $this->getUser();
        $model = UserAddress::detail($user['user_id'], $address_id);
        if ($model->remove($user)) {
            return $this->renderSuccess('删除成功');
        }
        return $this->renderError($model->getError() ?: '删除失败');
    }

}
