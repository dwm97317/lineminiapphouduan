<?php

namespace app\common\service\message\package;

use app\common\service\message\Basics;
use app\common\model\Setting as SettingModel;
use app\common\model\User;
use app\common\model\store\Shop;
use app\common\enum\OrderType as OrderTypeEnum;

/**
 * 消息通知服务 [订单入库成功]
 * @package app\common\service\message\order
 */
class Payorder extends Basics
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
        OrderTypeEnum::MASTER => 'pages/indexs/dairuku_xq/dairuku_xq',
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
        // dump($param);die;
        // 微信订阅消息通知用户
        // $this->onSendWxSubMsg();
        // 微信模板消息通知用户
        $this->onSendWxTplMsg();
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
        $orderInfo = $this->param;
        $orderType = OrderTypeEnum::MASTER;
        
        // 获取订阅消息配置
        $template = SettingModel::getItem('tplMsg', $orderInfo['wxapp_id'])['payorder'];
      
        $noticesetting = SettingModel::getItem('notice');
        $storesetting = SettingModel::getItem('store');
          
        if ($template['is_enable']==0) {
            return false;
        }
        if (empty($template['template_id'])) {
            return false;
        }
       
        //当入库不存在用户id，则不发送提醒；
        if(!isset($this->param['member_id'])){
             return false;
        }
        //判断是否采用H5方式；H5+小程序；
        // dump($this->param);die;
        if ($storesetting['client']['mode']==10) {
            return  $this->sendWxTplMsgForH5($orderInfo['wxapp_id'], [
            'touser' => $this->getGzhOpenidByUserId($this->param['member_id']),
            'template_id' => $template['template_id'],
            'data' => [
                $template['keywords'][0] => ['value' => $orderInfo['order_sn']],
                $template['keywords'][1] => ['value' => $orderInfo['userName']],
                $template['keywords'][2] => ['value' => $orderInfo['weight']],
                $template['keywords'][3] => ['value' => $orderInfo['volume']],
            ]
        ]);
        }else{
            return  $this->sendWxTplMsg($orderInfo['wxapp_id'], [
            'touser' => $this->getGzhOpenidByUserId($this->param['member_id']),
            'template_id' => $template['template_id'],
            'url' => "{$this->pageUrl[$orderType]}?id={$orderInfo['id']}&rtype=10",
            'miniprogram'=>[
                'appid' => '',
                'pagepath'=> "{$this->pageUrl[$orderType]}?id={$orderInfo['id']}&rtype=10"
            ],
            'data' => [
                $template['keywords'][0] => ['value' => $orderInfo['order_sn']],
                $template['keywords'][1] => ['value' => $orderInfo['member_id']],
                $template['keywords'][2] => ['value' => $orderInfo['weight']],
                $template['keywords'][3] => ['value' => $orderInfo['total_free']],
                $template['keywords'][4] => ['value' => getTime()],
            ]
            ]);
        }
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
    
    public function getShopByShopId($shop_id){
        return (new Shop())->where(['shop_id'=>$shop_id])->value('shop_name');
    }
}