<?php

namespace app\common\library\express;

/**
 * 快递接口
 * Interface ExpressInterface
 * @package app\common\library\express
 */
interface ExpressInterface
{
    /**
     * 创建快递订单
     * @param array $orderData 订单数据
     * @return array 返回运单号等信息
     */
    public function createOrder($orderData);

    /**
     * 获取面单数据
     * @param array $orderData 订单数据
     * @return array 返回面单HTML和相关数据
     */
    public function getWaybill($orderData);

    /**
     * 取消快递订单
     * @param string $waybillNo 运单号
     * @return bool
     */
    public function cancelOrder($waybillNo);

    /**
     * 查询快递轨迹
     * @param string $waybillNo 运单号
     * @return array
     */
    public function queryTrack($waybillNo);
}
