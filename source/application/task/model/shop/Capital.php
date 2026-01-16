<?php

namespace app\task\model\shop;

use app\common\model\Inpack as OrderModel;
// use app\common\service\Order as OrderService;

/**
 * 分销商订单模型
 * Class Apply
 * @package app\task\model\dealer
 */
class Capital extends OrderModel
{
    /**
     * 获取未结算的集运订单
     * @return false|\PDOStatement|string|\think\Collection
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getUnSettledList()
    {
        $list = $this->where('is_delete', '=', 0)
            ->where('is_settled', '=', 0)
            ->select();
        if ($list->isEmpty()) {
            return $list;
        }
        // 整理订单信息
        $with = ['goods' => ['refund']];
        return OrderService::getOrderList($list, 'order_master', $with);
    }


}