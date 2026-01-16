<?php

namespace app\store\model\store\shop;

use app\common\model\store\shop\ShopApply as ShopApplyModel;
use app\common\service\Message as MessageService;
use app\store\model\store\Shop as ShopModel;
use app\store\model\store\shop\Clerk;
use app\store\model\User;
/**
 * 仓库申请加盟模型
 * Class ShopApply
 * @package app\store\model\store\shop
 */
class ShopApply extends ShopApplyModel
{
    /**
     * 获取列表数据
     * @param string $search 仓库名称/手机号
     * @return \think\Paginator
     * @throws \think\exception\DbException
     */
    public function getList($search = '')
    {
        // 检索查询条件
        !empty($search) && $this->where('shop_name|linkman|phone', 'like', "%{$search}%");
        // 查询列表数据
        return $this
            ->order(['create_time' => 'desc'])
            ->paginate(15, false, [
                'query' => \request()->request()
            ]);
    }
    
        /**
     * 分销商入驻审核
     * @param $data
     * @return bool
     */
    public function submit($data)
    {
        if ($data['status'] == 2 && empty($data['reason'])) {
            $this->error = '请填写驳回原因';
            return false;
        }
        $this->transaction(function () use ($data) {
            if ($data['status'] == 1) {
                // 新增分销商用户
                $shopid = (new ShopModel())->insertGetId([
                    'shop_name' => $this['shop_name'],
                    'linkman' => $this['linkman'],
                    'phone' => $this['phone'],
                    'code' => $this['code'],
                    'address' => $this['address'],
                    'status' => 1,
                    'wxapp_id' => $this['wxapp_id'],
                    'create_time' => time(),
                    'update_time' => time(),
                ]);
                //设置员工权限
                (new Clerk())->add([
                    'shop_id' => $shopid,
                    'user_id' => $this['user_id'],
                    'real_name' => $this['linkman'],
                    'mobile' => $this['phone'],
                    'status' => 1,
                    'wxapp_id' => $this['wxapp_id'],
                    'clerk_type' => ['1,2,3'],
                ]);
                //将申请人设置为仓管员
                User::detail(['user_id'=> $this['user_id']])->where(['user_id'=> $this['user_id']])->update(['user_type' => 5]);
            }
            $this->allowField(true)->save($data);
            // 发送订阅消息
         
            // MessageService::send('dealer.apply', [
            //     'apply' => $this,               // 申请记录
            //     'user' => $this['user'],        // 用户信息
            // ]);
        });
        return true;
    }
    
     public function setDelete()
    {
        return $this->delete();
    }

    
    /**
     * 编辑记录
     * @param $data
     * @return bool|int
     */
    public function edit($data)
    {
        return $this->allowField(true)->save($data);
    }

    
}