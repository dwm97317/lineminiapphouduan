<?php

namespace app\common\enum\order;

use app\common\enum\EnumBasics;

/**
 * 订单支付方式枚举类
 * Class PayType
 * @package app\common\enum\order
 */
class PayType extends EnumBasics
{
    // 余额支付
    const BALANCE = 10;

    // 微信支付
    const WECHAT = 20;
    
    // 汉特支付
    const HANTEPAY = 30;
    
     // OMIPAY支付
    const OMIPAY = 40;

    /**
     * 获取枚举数据
     * @return array
     */
    public static function data()
    {
        return [
            self::BALANCE => [
                'name' => '余额支付',
                'value' => self::BALANCE,
            ],
            self::WECHAT => [
                'name' => '微信支付',
                'value' => self::WECHAT,
            ],
            self::HANTEPAY => [
                'name' => '汉特支付',
                'value' => self::HANTEPAY,
            ],
            self::OMIPAY => [
                'name' => 'OMIPAY支付',
                'value' => self::OMIPAY,
            ],
        ];
    }

}