<?php

namespace app\api\model\store\shop;

use app\common\model\store\shop\ShopApply as ShopApplyModel;
/**
 * 申请加盟模型
 * Class Order
 * @package app\api\model\store\shop
 */
class ShopApply extends ShopApplyModel
{


    /**
     * 是否为加盟商申请中
     * @param $user_id
     * @return bool
     * @throws \think\exception\DbException
     */
    public static function isApplying($user_id)
    {
        $detail = self::detail(['user_id' => $user_id]);
        return $detail ? ((int)$detail['status'] === 0) : false;
    }
    
    /**
     * 提交申请
     * @param $user
     * @param $name
     * @param $mobile
     * @return mixed
     * @throws \think\exception\DbException
     */
    public function submit($user, $param)
    {
        // 成为加盟商条件
        $config = Setting::getItem('condition');

        // 数据整理
        $data = [
            'user_id' => $user['user_id'],
            'linkman' => trim($param['username']),
            'phone' => trim($param['mobile']),
            'referee_id' => $param['referee_id'],
            'address'=> $param['address'],
            'shop_name' =>$param['city'],
            'code' =>$param['mail'],
            'status' => $config['become'],
            'create_time' => time(),
            'wxapp_id' => self::$wxapp_id,
        ];
        if ($config['become'] == 10) {
            $data['status'] = 0;
        } elseif ($config['become'] == 20) {
            $data['status'] = 1;
        }
        return $this->add($user, $data);
    }

    /**
     * 更新加盟商申请信息
     * @param $user
     * @param $data
     * @return mixed
     */
    private function add($user, $data)
    {
        // 更新记录
        return $this->transaction(function () use ($user, $data) {
            // 实例化模型
            $model = self::detail(['user_id' => $user['user_id']]) ?: $this;
            // 保存申请信息
            $model->save($data);
            // 无需审核，自动通过
            if ($data['status'] == 1) {
                // 新增分销商用户记录
                User::add($user['user_id'], [
                    'linkman' => $data['real_name'],
                    'phone' => $data['mobile'],
                    'referee_id' => $data['referee_id']
                ]);
            }
            return true;
        });
    }

}