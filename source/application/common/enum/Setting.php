<?php

namespace app\common\enum;

/**
 * 商城设置枚举类
 * Class Setting
 * @package app\common\enum
 */
class Setting extends EnumBasics
{
    // 系统设置
    const STORE = 'store';
     // 智能AI
    const AIIDENTIFY = 'aiidentify';
    // 交易设置
    const TRADE = 'trade';
    // 电脑端设置
    const ADMINSTYLE = 'adminstyle';
    // 仓管端设置
    const KEEPER = 'keeper';
    // 短信通知
    const SMS = 'sms';
    // 批次设置
    const BATCH = 'batch';
    //用户端样式设置
    const USERCLIENT = 'userclient';
    // 语言设置
    const LANG = 'lang';
    
     // 邮件通知
    const EMAIL = 'email';

    // 温馨提示
    const BANK = 'bank';
    
    // // 模板消息
    const TPL_MSG = 'tplMsg';

    // 上传设置
    const STORAGE = 'storage';
    const SERVICE = 'service';
    // 小票打印
    const PRINTER = 'printer';
    // 账户注册设置
    const REGISTER = 'register';
    // 满额包邮设置
    const FULL_FREE = 'full_free';
    //
    const PAYTYPE = 'paytype';
    // 充值设置
    const RECHARGE = 'recharge';

    // 积分设置
    const POINTS = 'points';

    // 订阅消息设置
    const SUBMSG = 'submsg';
    
    // 物流模板设置
    const NOTICE = 'notice';

    // 分类页模板
    const PAGE_CATEGORY_TEMPLATE = 'page_category_template';

    // LINE小程序设置
    const LINE_CONFIG = 'line_config';
    // LINE 消息通知
    const LINE_MESSAGING = 'line_messaging';
    // LINE Pay 支付
    const LINE_PAY = 'line_pay';
    // 客户联系配置
    const CUSTOMER_CONTACT = 'customer_contact';


    /**
     * 获取订单类型值
     * @return array
     */
    public static function data()
    {
        return [
            self::STORE => [
                'value' => self::STORE,
                'describe' => '系统设置',
            ],
            self::ADMINSTYLE => [
                'value' => self::ADMINSTYLE,
                'describe' => '电脑端设置',
            ],
            self::BATCH => [
                'value' => self::BATCH,
                'describe' => '批次设置',
            ],
            self::KEEPER => [
                'value' => self::KEEPER,
                'describe' => '仓管端设置',
            ],
            self::AIIDENTIFY => [
                'value' => self::AIIDENTIFY,
                'describe' => '智能AI',
            ],
            self::TRADE => [
                'value' => self::TRADE,
                'describe' => '交易设置',
            ],
            self::USERCLIENT => [
                'value' => self::USERCLIENT,
                'describe' => '用户端设置',
            ],
            self::SMS => [
                'value' => self::SMS,
                'describe' => '短信通知',
            ],
            self::PAYTYPE => [
                'value' => self::PAYTYPE,
                'describe' => '支付设置',
            ],
            self::LANG => [
                'value' => self::LANG,
                'describe' => '语言设置',
            ],
            self::EMAIL => [
                'value' => self::EMAIL,
                'describe' => '邮件通知',
            ],
            self::BANK => [
                'value' => self::BANK,
                'describe' => '温馨提示',
            ],
            self::TPL_MSG => [
                'value' => self::TPL_MSG,
                'describe' => '模板消息',
            ],
            self::STORAGE => [
                'value' => self::STORAGE,
                'describe' => '上传设置',
            ],
            self::NOTICE => [
                'value' => self::NOTICE,
                'describe' => '物流模板设置',
            ],
            self::SERVICE => [
                'value' => self::STORAGE,
                'describe' => '服务费设置',
            ],
            self::PRINTER => [
                'value' => self::PRINTER,
                'describe' => '小票打印',
            ],
            self::FULL_FREE => [
                'value' => self::FULL_FREE,
                'describe' => '满额包邮设置',
            ],
            self::RECHARGE => [
                'value' => self::RECHARGE,
                'describe' => '充值设置',
            ],
            self::POINTS => [
                'value' => self::POINTS,
                'describe' => '积分设置',
            ],
            self::SUBMSG => [
                'value' => self::SUBMSG,
                'describe' => '小程序订阅消息',
            ],
            self::PAGE_CATEGORY_TEMPLATE => [
                'value' => self::PAGE_CATEGORY_TEMPLATE,
                'describe' => '分类页模板',
            ],
            self::LINE_CONFIG => [
                'value' => self::LINE_CONFIG,
                'describe' => 'LINE小程序设置',
            ],
            self::LINE_MESSAGING => [
                'value' => self::LINE_MESSAGING,
                'describe' => 'LINE消息通知',
            ],
            self::LINE_PAY => [
                'value' => self::LINE_PAY,
                'describe' => 'LINE Pay支付设置',
            ],
            self::CUSTOMER_CONTACT => [
                'value' => self::CUSTOMER_CONTACT,
                'describe' => '客户联系配置',
            ],
        ];
    }



}