<?php

namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 付款单生成通知
 * Class Payorder
 * @package app\common\service\message\line
 */
class Payorder extends Basics
{
    protected $param = [];
    
    /**
     * 发送付款单生成通知
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
            'total_amount' => $orderInfo['total_amount'] ?? 0,
            'due_date' => $orderInfo['due_date'] ?? '',
            'remark' => $orderInfo['remark'] ?? '请及时支付',
            'pay_url' => $payUrl
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'payorder', $data);
    }
}
