<?php

namespace app\common\enum;

/**
 * 配送方式枚举类
 * Class DeliveryType
 * @package app\common\enum
 */
class BatchType extends EnumBasics
{
    // 空运
    const KONGYUN = 10;
    // 海运
    const HAIYUN = 20;
    // 陆运
    const LUYUN = 30;
    // 铁运
    const TIEYUN = 40;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data()
    {
        return [
            10 => [
                'name' => '空运',
                'value' => 10,
            ],
            20 => [
                'name' => '海运',
                'value' => 20,
            ],
            30 => [
                'name' => '陆运',
                'value' => 30,
            ],
            40 => [
                'name' => '铁运',
                'value' => 40,
            ],
        ];
    }

}