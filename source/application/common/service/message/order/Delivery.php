<?php

namespace app\common\service\message\order;

use app\common\service\message\Basics;
use app\common\model\User;
use app\common\model\Setting as SettingModel;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 消息通知服务 [订单发货]
 * Class Delivery
 * @package app\common\service\message\order
 */
class Delivery extends Basics
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
     * 订单页面链接
     * @var array
     */
    private $pageUrl = [
        OrderTypeEnum::MASTER => 'pages/indexs/my_dingdan_details/my_dingdan_details',
        OrderTypeEnum::SHARING => 'pages/sharing/order/detail/detail',
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
        $this->onSendWxSubMsg();
    }

    /**
     * 微信订阅消息通知用户
     * @return bool|mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function onSendWxSubMsg()
    {
        $orderInfo = $this->param['order'];
        $orderType = $this->param['order_type'];
        $wxappId = $orderInfo['wxapp_id'];
        
        // 获取订阅消息配置
        $template = SettingModel::getItem('submsg', $wxappId)['order']['delivery'];
        
        if (empty($template['template_id'])) {
            return false;
        }
        // 发送订阅消息
        return $this->sendWxSubMsg($wxappId, [
            'touser' =>$this->getOpenidByUserId($orderInfo['member_id']),
            'template_id' => $template['template_id'],
            'page' => "{$this->pageUrl[$orderType]}?id={$orderInfo['id']}&rtype=10",
            'miniprogram'=>[
               'appid' => '',
               'pagepath'=>"{$this->pageUrl[$orderType]}?id={$orderInfo['id']}&rtype=10",
            ],
            'data' => [
                // 订单号
                $template['keywords'][0] => ['value' => $orderInfo['order_sn']],
                // 商品名称
                $template['keywords'][1] => ['value' => $orderInfo['updated_time']],
                // 收货人
                $template['keywords'][2] => ['value' => $orderInfo['t_name']],
                // 收货地址
                $template['keywords'][3] => ['value' => $orderInfo['t_order_sn']],
            ]
        ]);
    }

    public function getOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('open_id');
    }

    /**
     * 格式化商品名称
     * @param $goodsData
     * @return string
     */
    private function getFormatGoodsName($goodsData)
    {
        return $this->getSubstr($goodsData[0]['goods_name']);
    }
}