<?php

namespace app\common\service\message;

use app\common\model\Wxapp as WxappModel;
use app\common\model\Setting as SettingModel;
use app\common\library\sms\Driver as SmsDriver;
use app\common\library\wechat\WxSubMsg;
use app\common\library\wechat\WxTplMsg;

/**
 * 消息通知服务[基类]
 * Class Basics
 * @package app\common\service\message
 */
abstract class Basics extends \app\common\service\Basics
{
    // 参数列表
    protected $param = [];

    /**
     * 发送消息通知
     * @param array $param 参数
     * @return mixed
     */
    abstract public function send($param);

    /**
     * 发送短信提醒
     * @param $msgType
     * @param $templateParams
     * @param $wxappId
     * @return bool
     * @throws \think\Exception
     */
    protected function sendSms($msgType, $templateParams, $wxappId)
    {
        $smsConfig = SettingModel::getItem('sms', $wxappId);
        return (new SmsDriver($smsConfig))->sendSms($msgType, $templateParams);
    }

   /**
     * 发送登录短信提醒
     * @param $msgType
     * @param $templateParams
     * @param $wxappId
     * @return bool
     * @throws \think\Exception
     */
    protected function sendCaphaSms($msgType, $templateParams, $wxappId=10001)
    {
        $smsConfig = SettingModel::getItem('sms', $wxappId);
        $smsConfig['engine']['aliyun']['order_pay']['template_code'] = 'SMS_224655142';
        $smsConfig['engine']['aliyun']['order_pay']['accept_phone'] = $templateParams['mobile'];
        $smsConfig['engine']['aliyun']['order_pay']['is_enable'] = '1';
        unset($templateParams['mobile']);
        return (new SmsDriver($smsConfig))->sendCachaSms($msgType, $templateParams);
    }

    /**
     * 发送微信订阅消息
     * @param $wxappId
     * @param $params
     * @return mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    protected function sendWxSubMsg($wxappId, $params)
    {
        // 获取小程序配置
        $wxConfig = WxappModel::getWxappCache($wxappId);
        // 请求微信api执行发送
        $WxSubMsg = new WxSubMsg($wxConfig['app_id'], $wxConfig['app_secret']);
        return $WxSubMsg->sendTemplateMessage($params);
    }
    
    /**
     * 发送微信模板消息
     * @param $wxappId
     * @param $params
     * @return mixed
     * @throws \app\common\exception\BaseException
     * @throws \think\Exception
     * @throws \think\exception\DbException
     */
    protected function sendWxTplMsg($wxappId, $params)
    {
        
        // 获取小程序配置
        $wxConfig = WxappModel::getWxappCache($wxappId);
        // dump($wxConfig);die;
        // 请求微信api执行发送
        $WxSubMsg = new WxTplMsg($wxConfig['app_id'], $wxConfig['app_secret'],$wxConfig['app_wxappid'],$wxConfig['app_wxsecret'],$wxConfig['wx_type']);

        return $WxSubMsg->sendWxTemplateMessage($params);
    }
    
    
    
    protected function sendWxTplMsgForH5($wxappId, $params)
    {
        
        // 获取小程序配置
        $wxConfig = WxappModel::getWxappCache($wxappId);
   
        // 请求微信api执行发送
        $WxSubMsg = new WxTplMsg($wxConfig['app_id'], $wxConfig['app_secret'],$wxConfig['app_wxappid'],$wxConfig['app_wxsecret']);
        
        return $WxSubMsg->sendWxTemplateMessageForH5($params);
    }

    /**
     * 字符串截取前20字符
     * [用于兼容thing数据类型]
     * @param $content
     * @param int $length
     * @return bool|string
     */
    protected function getSubstr($content, $length = 20)
    {
        return str_substr($content, $length);
    }

}