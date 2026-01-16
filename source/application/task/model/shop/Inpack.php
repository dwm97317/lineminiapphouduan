<?php

namespace app\task\model\shop;

use app\common\model\Inpack as InpackModel;
use app\common\service\Order as OrderService;

/**
 * 加盟商订单模型
 * Class Apply
 * @package app\task\model\dealer
 */
class Inpack extends InpackModel
{
    /**
     * 获取未结算的集运订单
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUnSettledList($wxappid)
    {
   
        $list = (new InpackModel())
            ->where('is_delete', '=', 0)
            ->where('is_settled', '=', 0)
            ->where('status', '=', 8)
            ->where('is_pay', '=', 1)
            ->where('wxapp_id',$wxappid)
            ->limit(10)
            ->select();
        if ($list->isEmpty()) {
            return $list;
        }
        // 整理订单信息
        $with = [];
        return OrderService::getOrderList($list, 'order_master', $with);
    }
    
    
    /**
     * 获取滞留件的集运订单
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getdetainedList($wxappid){
        $list = $this
            ->where('is_delete', '=', 0)
            ->where('Inpack_type',0)
            ->where('status', '=', 7)
            ->where('wxapp_id',$wxappid)
            ->limit(10)
            ->select();
        if ($list->isEmpty()) {
            return $list;
        }
        $with = [];
        //  dump(OrderService::getOrderList($list, 'order_master', $with));die;
        return $list;
        // OrderService::getOrderList($list, 'order_master', $with);
        
    }

    /**
     * 标记订单已失效(批量)
     * @param $ids
     * @return false|int
     */
    public function setInvalid($ids)
    {
        return $this->isUpdate(true)
            ->save(['is_invalid' => 1], ['id' => ['in', $ids]]);
    }

}