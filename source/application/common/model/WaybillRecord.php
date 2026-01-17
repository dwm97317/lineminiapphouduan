<?php

namespace app\common\model;

use think\Model;

/**
 * 面单打印记录模型（基类）
 * Class WaybillRecord
 * @package app\common\model
 */
class WaybillRecord extends Model
{
    protected $name = 'waybill_record';
    protected $pk = 'id';

    protected static $wxapp_id;

    /**
     * 操作类型
     */
    const OPERATION_PRINT = 1;  // 打印
    const OPERATION_ORDER = 2;  // 只下单

    /**
     * 获取器：格式化打印时间
     * @param $value
     * @return false|string
     */
    public function getPrintTimeAttr($value)
    {
        return $value > 0 ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 获取器：格式化创建时间
     * @param $value
     * @return false|string
     */
    public function getCreatedTimeAttr($value)
    {
        return $value > 0 ? date('Y-m-d H:i:s', $value) : '';
    }

    /**
     * 获取器：操作类型文本
     * @param $value
     * @param $data
     * @return string
     */
    public function getOperationTypeTextAttr($value, $data)
    {
        $types = [
            self::OPERATION_PRINT => '打印',
            self::OPERATION_ORDER => '只下单',
        ];
        return $types[$data['operation_type']] ?? '未知';
    }
}
