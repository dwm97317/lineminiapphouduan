<?php

namespace app\store\controller\data;

use app\store\controller\Controller;
use app\store\model\UserAddress as UserAddress;
class Address extends Controller
{
    private $model;

    /**
     * 构造方法
     * @throws \app\common\exception\BaseException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function _initialize()
    {
        parent::_initialize();
        $this->model = new UserAddress;
        $this->view->engine->layout(false);
    }

     public function AddressList($title=''){
        $user_id = input('user_id');
        if(!$user_id){
            $this->error('请选择集运单');
            return false;
        }
        $list = (new UserAddress())->getList($user_id);
       return $this->fetch('list', compact('list'));
    }
    
    //所有用户的包裹
     public function AddressAllList($title=''){
        $user_id = input('user_id');
        if(!$user_id){
            $this->error('请选择集运单');
            return false;
        }
        $list = (new UserAddress())->getAllList($user_id,$title);
       return $this->fetch('list', compact('list'));
    }
    
    //所有用户的包裹
     public function AddressAll($title=''){
        $list = (new UserAddress())->getAll($title);
       return $this->fetch('list', compact('list'));
    }

}