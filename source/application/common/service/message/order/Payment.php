<?php

namespace app\common\service\message\order;

use app\common\service\message\Basics;
use app\common\model\User;
use app\common\model\Setting as SettingModel;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\model\Wxapp;

/**
 * 消息通知服务 [订单支付成功]
 * Class Payment
 * @package app\common\service\message\order
 */
class Payment extends Basics
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
        // 微信模板消息通知用户
        $this->onSendWxTplMsg();
    }

    /**
     * 短信通知商家
     * @return bool
     * @throws \think\Exception
     */
    private function onSendSms()
    {
        // $orderInfo = $this->param['order'];
        $wxappId = $orderInfo['wxapp_id'];
        return $this->sendSms('order_pay', ['order_no' => $orderInfo['order_no']], $wxappId);
    }
    
    /**
     * 微信模板消息通知用户
     * @return bool|mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    private function onSendWxTplMsg()
    {
 
        $orderInfo = $this->param['order'];
        $orderType = $this->param['order_type'];
       
        // 获取订阅消息配置
        $storesetting = SettingModel::getItem('store');
        $template = SettingModel::getItem('tplMsg', $orderInfo['wxapp_id'])['delivery'];
        if ($template['is_enable']==0) {
            return false;
        }
        if (empty($template['template_id'])) {
            return false;
        }
        if ($storesetting['client']['mode']==10) {
            // dump($this->param['order']['member_id']);die;
           return  $this->sendWxTplMsgForH5($orderInfo['wxapp_id'], [
            'touser' => $this->getGzhOpenidByUserId($this->param['order']['member_id']),
            'template_id' => $template['template_id'],
            // 'url' => "{$this->pageUrl[$orderType]}?id={$orderInfo['id']}",
            'data' => [
                $template['keywords'][0] => ['value' => '恭喜您'.$orderInfo['userName']],
                $template['keywords'][1] => ['value' => $orderInfo['t_order_sn']?$orderInfo['t_order_sn']:$orderInfo['order_sn']],
                $template['keywords'][2] => ['value' => date("Y-m-d H:i:s",time())],
                $template['keywords'][3] => ['value' => $orderInfo['remark']],
                $template['keywords'][4] => ['value' => ''],
            ]
        ]);
        }else{
           return $this->sendWxTplMsg($orderInfo['wxapp_id'], [
                'touser' => $this->getGzhOpenidByUserId($this->param['order']['member_id']),
                'template_id' => $template['template_id'],
                'url' => "{$this->pageUrl[$orderType]}?id={$orderInfo['id']}",
                'miniprogram'=>[
                   'appid' => '',
                   'pagepath'=>"{$this->pageUrl[$orderType]}?id={$orderInfo['id']}&rtype=10",
                ],
                'data' => [
                    $template['keywords'][0] => ['value' => '恭喜您'.$orderInfo['userName']],
                    $template['keywords'][1] => ['value' => $orderInfo['t_order_sn']?$orderInfo['t_order_sn']:$orderInfo['order_sn']],
                    $template['keywords'][2] => ['value' => date("Y-m-d H:i:s",time())],
                    $template['keywords'][3] => ['value' => $orderInfo['remark']],
                    $template['keywords'][4] => ['value' => ''],
                ]
            ]);
        }
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
        $template = SettingModel::getItem('submsg', $wxappId)['order']['pay'];
        if (empty($template['template_id'])) {
            return false;
        }
        
        // 发送订阅消息
        return $this->sendWxSubMsg($wxappId, [
            'touser' => $this->getOpenidByUserId($orderInfo['member_id']),
            'template_id' => $template['template_id'],
            'page' => "{$this->pageUrl[$orderType]}?id={$orderInfo['id']}&rtype=10",
            'data' => [
                // 订单编号
                $template['keywords'][0] => ['value' => $orderInfo['t_order_sn']?$orderInfo['t_order_sn']:$orderInfo['order_sn']],
                // 下单时间
                $template['keywords'][1] => ['value' => $orderInfo['total_free']],
                // 订单金额
                $template['keywords'][2] => ['value' => '待支付'],
                // 商品名称
                $template['keywords'][3] => ['value' => $orderInfo['created_time']],
            ]
        ]);
    }
    
    public function getGzhOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('gzh_openid');
    }
    
    public function getUnionidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('union_id');
    }

    public function getOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('open_id');
    }

}