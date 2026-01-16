<?php

namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 发货通知
 * Class Sendpack
 * @package app\common\service\message\line
 */
class Sendpack extends Basics
{
    protected $param = [];
    
    /**
     * 发送发货通知
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
        
        // 构建详情页链接
        $detailUrl = $this->buildLiffUrl(
            '/order/detail',
            ['order_sn' => $orderInfo['order_sn'] ?? ''],
            $wxappId
        );
        
        // 判断是否有转单信息，优先使用转单信息
        $trackingNumber = !empty($orderInfo['t2_order_sn']) ? $orderInfo['t2_order_sn'] : ($orderInfo['t_order_sn'] ?? '');
        $carrierName = !empty($orderInfo['t2_name']) ? $orderInfo['t2_name'] : ($orderInfo['t_name'] ?? '');
        
        // 构建模板数据
        $data = [
            'order_sn' => $orderInfo['order_sn'] ?? '',
            't_order_sn' => $trackingNumber,
            'weight' => $orderInfo['weight'] ?? 0,
            't_name' => $carrierName,
            'send_time' => $orderInfo['send_time'] ?? date('Y-m-d H:i:s'),
            'tracking_url' => $orderInfo['tracking_url'] ?? $detailUrl,
            'detail_url' => $detailUrl
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'sendpack', $data);
    }
}
