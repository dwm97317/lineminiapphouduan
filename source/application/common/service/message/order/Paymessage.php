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
class Paymessage extends Basics
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
        OrderTypeEnum::MASTER => 'pages/cangkuyuans/cangkuyuan_jianlianqd/cangkuyuan_jianlianqd',
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
        $data = $this->param;
   
        // 获取订阅消息配置
        $template = SettingModel::getItem('tplMsg', $data['wxapp_id'])['paymessage'];
        if ($template['is_enable']==0) {
            return false;
        }
        if (empty($template['template_id'])) {
            return false;
        }
    
       return $this->sendWxTplMsg($data['wxapp_id'], [
            'touser' => $this->getOpenidByUserId($data['clerkid']),//仓管id，找到仓管员，
            'template_id' => $template['template_id'],
            'url' => "",
            'miniprogram'=>[
               'appid' => '',
               'pagepath'=> "{$this->pageUrl[10]}?id={$data['packid']}&type=0&rtype=10" 
            ],
            'data' => [
                $template['keywords'][0] => ['value' => '客户订单已完成支付，请准备发货'],
                $template['keywords'][1] => ['value' => $data['packid']],  // 订单编号：{{keyword1.DATA}}
                $template['keywords'][2] => ['value' => $data['amount']],// 支付金额：{{keyword2.DATA}}
                $template['keywords'][3] => ['value' => $data['paytime']],// 支付时间：{{keyword3.DATA}}
                $template['keywords'][4] => ['value' => $data['remark']],  // 备注：{{remark.DATA}}
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