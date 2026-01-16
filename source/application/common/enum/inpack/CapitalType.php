<?php

namespace app\common\enum\inpack;

use app\common\enum\EnumBasics;

/**
 * 结算场景枚举类
 * Class Scene
 * @package app\common\enum\inpack
 */
class CapitalType extends EnumBasics
{
    // 寄件收入
    const SENDPAK = 10;

    // 收件收入
    const PICKPAK = 20;

    // 提现支出
    const OUT = 30;


    /**
     * 获取订单类型值
     * @return array
     */
    public static function data()
    {
        return [
            self::SENDPAK => [
                'name' => '寄件收入',
                'value' => self::SENDPAK,
                'describe' => '寄件收入：%s',
            ],
            self::PICKPAK => [
                'name' => '收件收入',
                'value' => self::PICKPAK,
                'describe' => '收件收入：%s',
            ],
            self::OUT => [
                'name' => '提现支出',
                'value' => self::OUT,
                'describe' => '提现支出:%s',
            ],
        ];
    }

}