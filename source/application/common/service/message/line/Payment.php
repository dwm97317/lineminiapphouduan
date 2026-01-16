<?php

namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 支付成功通知
 * Class Payment
 * @package app\common\service\message\line
 */
class Payment extends Basics
{
    protected $param = [];
    
    /**
     * 发送支付成功通知
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
        
        // 构建模板数据
        $data = [
            'order_sn' => $orderInfo['order_sn'] ?? '',
            'total_free' => $orderInfo['total_free'] ?? 0,
            'pay_time' => $orderInfo['pay_time'] ?? date('Y-m-d H:i:s'),
            'remark' => $orderInfo['remark'] ?? '支付成功',
            'order_url' => $detailUrl
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'payment', $data);
    }
}
