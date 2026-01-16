<?php

namespace app\web\model;

use app\common\model\UserAddress as UserAddressModel;

/**
 * 用户收货地址模型
 * Class UserAddress
 * @package app\common\model
 */
class UserAddress extends UserAddressModel
{
    /**
     * 隐藏字段
     * @var array
     */
    protected $hidden = [
        'wxapp_id',
        'create_time',
        'update_time'
    ];

    /**
     * @param $user_id
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public function getList($user_id,$keyword='')
    {
        $keywords = [];
        if( $keyword && is_string($keyword)){
            $keywords['name'] = $keyword ;
        }else if (is_numeric($keyword)){
            $keywords['phone'] = $keyword ;
        }
        return self::where(['user_id'=>$user_id])->where($keywords)->where('addressty',0)->select();
    }
    
    /**
     * @param $user_id
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public function getjList($user_id,$keyword='')
    {
        $keywords = [];
        if( $keyword && is_string($keyword)){
            $keywords['name'] = $keyword ;
        }else if (is_numeric($keyword)){
            $keywords['phone'] = $keyword ;
        }
        return self::where(['user_id'=>$user_id])->where($keywords)->where('addressty',1)->select();
    }
    
    /**
     * @param $user_id
     * @return false|static[]
     * @throws \think\exception\DbException
     */
    public function getZList($keyword='')
    {
        $keywords = [];
        if( $keyword && is_string($keyword)){
            $keywords['name'] = $keyword ;
        }else if (is_numeric($keyword)){
            $keywords['phone'] = $keyword ;
        }
        return $this->where($keywords)->where('address_type',2)->select();
    }


    /**
     * 新增收货地址
     * @param User $user
     * @param $data
     * @return mixed
     */
    public function add($user, $data)
    {
        return $this->transaction(function () use ($user, $data) {
            // dump($user);die;
            // 添加收货地址
            $this->allowField(true)->save([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'country_id' => isset($data['country_id'])?$data['country_id']:'',
                'country' => isset($data['country'])?$data['country']:'',
                'province' => isset($data['province'])?$data['province']:'',
                'city' => isset($data['city'])?$data['city']:'',
                'region' => isset($data['region'])?$data['region']:0,
                'email' => isset($data['email'])?$data['email']:'',
                'door' => isset($data['door'])?$data['door']:'',
                'tel_code' => isset($data['tel_code'])?$data['tel_code']:'',
                'addressty' => isset($data['addressty'])?$data['addressty']:0,
                'detail' => isset($data['detail'])?$data['detail']:'',
                'user_id' => $user['user_id'],
                'wxapp_id' => self::$wxapp_id
            ]);
            // 设为默认收货地址
            // !$user['address_id'] && $user->save(['address_id' => $this['address_id']]);
            return true;
        });
    }

   /**
     * 编辑收货地址
     * @param $data
     * @return false|int
     */
    public function edit($data)
    {
        // 整理地区信息
        // dump($data);die;
        // $region = explode(',', $data['region']);
        // 更新收货地址
        return $this->allowField(true)->save([
                'name' => $data['name'],
                'phone' => $data['phone'],
                'country_id' => isset($data['country_id'])?$data['country_id']:'',
                'identitycard' => isset($data['identitycard'])?$data['identitycard']:'',
                'clearancecode' => isset($data['clearancecode'])?$data['clearancecode']:'',
                'country' => isset($data['country'])?$data['country']:"",
                'province' => isset($data['province'])?$data['province']:0,
                'city' => isset($data['city'])?$data['city']:'',
                'region' => isset($data['region'])?$data['region']:0,
                'street' => isset($data['street'])?$data['street']:'',
                'email' => isset($data['email'])?$data['email']:'',
                'door' => isset($data['door'])?$data['door']:'',
                'tel_code' => isset($data['tel_code'])?$data['tel_code']:'',
                'detail' => isset($data['detail'])?$data['detail']:'',
                
            ]) !== false;
    }

    /**
     * 设为默认收货地址
     * @param User $user
     * @return int
     */
    public function setDefault($user)
    {
        // 设为默认地址
        return $user->save(['address_id' => $this['address_id']]);
    }

    /**
     * 删除收货地址
     * @param User $user
     * @return int
     */
    public function remove($user)
    {
        // 查询当前是否为默认地址
        $user['address_id'] == $this['address_id'] && $user->save(['address_id' => 0]);
        return $this->delete();
    }

    /**
     * 收货地址详情
     * @param $user_id
     * @param $address_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($user_id, $address_id)
    {
        return self::get(compact('user_id', 'address_id'));
    }

}
