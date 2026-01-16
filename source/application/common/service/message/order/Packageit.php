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
class Packageit extends Basics
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
        $template = SettingModel::getItem('tplMsg', $data['wxapp_id'])['packageit'];
        if ($template['is_enable']==0) {
            return false;
        }
        if (empty($template['template_id'])) {
            return false;
        }
// dump($data['clerkid']);die;
       return $this->sendWxTplMsgForH5($data['wxapp_id'], [
            'touser' => $this->getGzhOpenidByUserId($data['clerkid']),//仓管id，找到仓管员，
            'template_id' => $template['template_id'],
            'url' => "",
            'miniprogram'=>[
               'appid' => '',
               'pagepath'=> "{$this->pageUrl[10]}?id={$data['packid']}&type=0&rtype=10" 
            ],
            'data' => [
                $template['keywords'][0] => ['value' => '客户提交了一个新的打包任务'],
                $template['keywords'][1] => ['value' => $data['nickName']],// 用户备注名：{{keyword1.DATA}}
                $template['keywords'][2] => ['value' => $data['userCode']],// 用户会员账号：{{keyword2.DATA}}
                $template['keywords'][3] => ['value' => $data['packtime']],// 用户打包申请时间：{{keyword3.DATA}}
                $template['keywords'][4] => ['value' => $data['countpack']], // 用户申请打包数量：{{keyword4.DATA}}
                $template['keywords'][5] => ['value' => $data['packid']], // 包裹出库编号：{{keyword5.DATA}}
                $template['keywords'][6] => ['value' => $data['remark']], // 备注：{{keyword5.DATA}}
            ]
        ]);
    }
    
    public function getOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('open_id');
    }
    
    public function getGzhOpenidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('gzh_openid');
    }
    
    public function getUnionidByUserId($user_id){
        return User::where(['user_id'=>$user_id])->value('union_id');
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