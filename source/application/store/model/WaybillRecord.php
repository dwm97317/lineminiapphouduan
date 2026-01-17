<?php

namespace app\store\model;

use app\common\model\WaybillRecord as WaybillRecordModel;

/**
 * 面单打印记录模型
 * Class WaybillRecord
 * @package app\store\model
 */
class WaybillRecord extends WaybillRecordModel
{
    /**
     * 添加打印记录
     * @param array $data
     * @return bool|int
     */
    public static function add($data)
    {
        $record = new static;
        $record->save([
            'inpack_id' => $data['inpack_id'],
            'order_sn' => $data['order_sn'],
            'express_type' => $data['express_type'],
            'express_name' => $data['express_name'],
            'waybill_no' => $data['waybill_no'] ?? '',
            'operation_type' => $data['operation_type'] ?? 1, // 1:打印 2:只下单
            'operator_id' => $data['operator_id'] ?? 0,
            'operator_name' => $data['operator_name'] ?? '',
            'print_time' => time(),
            'api_response' => $data['api_response'] ?? '',
            'remark' => $data['remark'] ?? '',
            'wxapp_id' => $data['wxapp_id'] ?? self::$wxapp_id,
            'created_time' => time(),
        ]);
        return $record->getKey();
    }

    /**
     * 获取订单的打印历史
     * @param int $inpackId
     * @return array
     */
    public static function getHistory($inpackId)
    {
        return static::where('inpack_id', $inpackId)
            ->order('created_time', 'desc')
            ->select();
    }

    /**
     * 获取最后一次打印记录
     * @param int $inpackId
     * @return static|null
     */
    public static function getLastRecord($inpackId)
    {
        return static::where('inpack_id', $inpackId)
            ->order('created_time', 'desc')
            ->find();
    }
}
