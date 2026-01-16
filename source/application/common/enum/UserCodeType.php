<?php

namespace app\common\enum;

/**
 * 配送方式枚举类
 * Class DeliveryType
 * @package app\common\enum
 */
class UserCodeType extends EnumBasics
{
    // 纯数字
    const SHUZI = 10;
    // 纯子母
    const ZIMU = 20;
    // 数字加子母
    const SHUMU = 30;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data()
    {
        return [
            self::SHUZI => [
                'name' => '纯数字模式',
                'value' => self::SHUZI,
            ],
            self::ZIMU => [
                'name' => '纯英文模式',
                'value' => self::ZIMU,
            ],
            self::SHUMU => [
                'name' => '数字英文混合模式',
                'value' => self::SHUMU,
            ],
        ];
    }

}