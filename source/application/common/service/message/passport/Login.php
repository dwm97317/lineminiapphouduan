<?php

namespace app\common\service\message\passport;

use app\common\service\message\Basics;
use app\common\model\User;
use app\common\model\Setting as SettingModel;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 消息通知服务 [订单支付成功]
 * Class Payment
 * @package app\common\service\message\order
 */
class Login extends Basics
{
    /**
     * 参数列表
     * @var array
     */
    protected $param = [
        'order' => [],
        'order_type' => OrderTypeEnum::MASTER,
    ];

   
    /**
     * 发送消息通知
     * @param array $param
     * @return mixed|void
     * @throws \think\Exception
     */
    public function send($param)
    {
        // 记录参数
        $this->param = $param;
        // 微信订阅消息通知用户
        $this->onSendSms();
    }

    /**
     * 短信通知商家
     * @return bool
     * @throws \think\Exception
     */
    private function onSendSms()
    {
        $orderInfo = $this->param;
        return $this->sendCaphaSms('order_pay', ['mobile' => $orderInfo['mobile'],'code'=>$orderInfo['code']]);
    }

}