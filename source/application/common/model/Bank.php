<?php
namespace app\common\model;

/**
 * 订单收货地址模型
 * Class OrderAddress
 * @package app\common\model
 */
class Bank extends BaseModel
{
    protected $name = 'bank';
    protected $updateTime = false;
   /**
     * 物流公司详情
     * @param $printer_id
     * @return null|static
     * @throws \think\exception\DbException
     */
    public static function detail($printer_id)
    {
        return self::get($printer_id);
    }
}
