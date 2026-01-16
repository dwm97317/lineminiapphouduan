<?php

namespace app\common\service;

use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 订单服务类
 * Class Order
 * @package app\common\service
 */
class Order
{
    /**
     * 订单模型类
     * @var array
     */
    private static $orderModelClass = [
        OrderTypeEnum::MASTER => 'app\common\model\Order',
        OrderTypeEnum::SHARING => 'app\common\model\sharing\Order',
        OrderTypeEnum::TRAN => 'app\common\model\Inpack'
    ];

    /**
     * 生成订单号
     * @return string
     */
    public static function createOrderNo()
    {
        return date('Ymd') . substr(implode(NULL, array_map('ord', str_split(substr(uniqid(), 7, 13), 1))), 0, 8);
    }

    /**
     * 整理订单列表 (根据order_type获取不同类型的订单记录)
     * @param \think\Collection|\think\Paginator $data 数据源
     * @param string $orderIndex 订单记录的索引
     * @param array $with 关联查询
     * @return mixed
     */
    public static function getOrderList($data, $with = [])
    {
        // 整理订单id
      
        $model = self::model(OrderTypeEnum::TRAN);
     
        foreach ($data as $key =>$val) {
          $data[$key]['inpack'] = $model::detail($val['order_id']);
        }
  
        return $data;
    }

    /**
     * 获取订单详情 (根据order_type获取不同类型的订单详情)
     * @param $orderId
     * @param int $orderType
     * @return mixed
     */
    // public static function getOrderDetail($orderId, $orderType = OrderTypeEnum::MASTER)
    // {
    //     $model = self::model($orderType);
    //     return $model::detail($orderId);
    // }

    /**
     * 根据订单类型获取对应的订单模型类
     * @param int $orderType
     * @return mixed
     */
    // public static function model($orderType = OrderTypeEnum::MASTER)
    // {
    //     static $models = [];
    //     if (!isset($models[$orderType])) {
    //         $models[$orderType] = new self::$orderModelClass[$orderType];
    //     }
    //     return $models[$orderType];
    // }
    
    /**
     * 根据订单类型获取对应的订单模型类
     * @param int $orderType
     * @return mixed
     */
    public static function model($orderType = OrderTypeEnum::TRAN)
    {
        static $models = [];
        if (!isset($models[$orderType])) {
            $models[$orderType] = new self::$orderModelClass[$orderType];
        }
        return $models[$orderType];
    }

}