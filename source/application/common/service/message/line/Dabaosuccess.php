<?php

namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 打包完成通知
 * Class Dabaosuccess
 * @package app\common\service\message\line
 */
class Dabaosuccess extends Basics
{
    protected $param = [];
    
    /**
     * 发送打包完成通知
     * @param array $param 参数
     * @return bool
     */
    public function send($param)
    {
        $this->param = $param;
        return $this->onSendLineMsg();
    }
    
    /**
     * 发送 LINE 消息
     * @return bool
     */
    private function onSendLineMsg()
    {
        $orderInfo = $this->param;
        $wxappId = $orderInfo['wxapp_id'];
        
        // 获取用户 LINE ID
        $lineUserId = $this->getLineUserIdByUserId($orderInfo['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        // 构建支付页链接
        $payUrl = $this->buildLiffUrl(
            '/order/payment',
            ['order_id' => $orderInfo['order_id'] ?? 0],
            $wxappId
        );
        
        // 构建模板数据
        $data = [
            'order_sn' => $orderInfo['order_sn'] ?? '',
            'pack_count' => $orderInfo['pack_count'] ?? 0,
            'weight' => $orderInfo['weight'] ?? 0,
            'volume' => $orderInfo['volume'] ?? 0,
            'pay_url' => $payUrl
        ];
        
        // 添加图片（如果有）
        if (!empty($orderInfo['images'])) {
            $data['images'] = $orderInfo['images'];
        } elseif (!empty($orderInfo['packageimage'])) {
            $data['packageimage'] = $orderInfo['packageimage'];
        }
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'dabaosuccess', $data);
    }
}
