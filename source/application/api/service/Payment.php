<?php

namespace app\api\service;

use app\api\model\Wxapp as WxappModel;
use app\api\model\Setting as SettingModel;

use app\common\library\wechat\WxPay;
use app\common\enum\OrderType as OrderTypeEnum;
use app\common\enum\order\PayType as PayTypeEnum;
use app\common\library\payment\HantePay\hantePay;
use app\common\library\payment\Omipay\Omipay;
class Payment
{
    /**
     * 构建订单支付参数
     * @param $user
     * @param $order
     * @param $payType
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public static function orderPayment($user, $order, $payType)
    {
        if ($payType == PayTypeEnum::WECHAT) {
            return self::wechat(
                $user,
                $order['order_id'],
                $order['order_no'],
                $order['pay_price'],
                OrderTypeEnum::MASTER
            );
        }
        return [];
    }

    /**
     * 构建微信支付
     * @param \app\api\model\User $user
     * @param $orderId
     * @param $orderNo
     * @param $payPrice
     * @param int $orderType
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public static function wechat(
        $user,
        $orderId,
        $orderNo,
        $payPrice,
        $orderType = OrderTypeEnum::MASTER
    )
    {
        // 统一下单API
        $wxConfig = WxappModel::getWxappCache($user['wxapp_id']);
        // dump($user);die;
        //获取当前使用的是元/美元
        $pricemode = SettingModel::getItem('store')['price_mode']['mode'];
        $paytype = SettingModel::getItem('paytype');
        
        switch ($pricemode) {
            case '20':
                $payPrice = round($payPrice * $paytype['rmbtousdollar'],2);
                break;
            
            default:
                $payPrice = round($payPrice * 1,2);
                break;
        }
        $WxPay = new WxPay($wxConfig);
        $payment = $WxPay->unifiedorder($orderNo, $user['open_id'], $payPrice, $orderType);
        return $payment;
    }
    
     /**
     * 构建汉特支付
     * @param \app\api\model\User $user
     * @param $orderId
     * @param $orderNo
     * @param $payPrice
     * @param int $orderType
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public static function Hantepay(
        $user,
        $orderId,
        $orderNo,
        $payPrice,
        $orderType = OrderTypeEnum::TRAN
    )
    {
        // 统一下单API
        $wxConfig = WxappModel::getWxappCache($user['wxapp_id']);
        $hantePay = new hantePay($wxConfig);
        $payment = $hantePay->unifiedorder($orderNo,$payPrice, $user['open_id'],$user, $orderType);
        return $payment;
    }
    
    /**
     * 构建OMIPAY支付
     * @param \app\api\model\User $user
     * @param $orderId
     * @param $orderNo
     * @param $payPrice
     * @param int $orderType
     * @return array
     * @throws \app\common\exception\BaseException
     * @throws \think\exception\DbException
     */
    public static function Omipay(
        $user,
        $orderId,
        $orderNo,
        $payPrice,
        $orderType = OrderTypeEnum::TRAN
    )
    {
        // 统一下单API
        $wxConfig = WxappModel::getWxappCache($user['wxapp_id']);
        $Omipay = new Omipay($wxConfig);
        $payment = $Omipay->unifiedorder($orderNo,$payPrice, $user['open_id'],$user, $orderType);
        return $payment;
    }

}