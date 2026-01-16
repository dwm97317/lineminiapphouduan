<?php

namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 出库申请通知
 * Class Outapply
 * @package app\common\service\message\line
 */
class Outapply extends Basics
{
    protected $param = [];
    
    /**
     * 发送出库申请通知
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
        $applyInfo = $this->param;
        $wxappId = $applyInfo['wxapp_id'];
        
        // 获取用户 LINE ID
        $lineUserId = $this->getLineUserIdByUserId($applyInfo['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        // 构建详情页链接
        $detailUrl = $this->buildLiffUrl(
            '/apply/detail',
            ['apply_id' => $applyInfo['apply_id'] ?? 0],
            $wxappId
        );
        
        // 构建模板数据
        $data = [
            'apply_sn' => $applyInfo['apply_sn'] ?? '',
            'package_count' => $applyInfo['package_count'] ?? 0,
            'status' => $applyInfo['status'] ?? '待审核',
            'remark' => $applyInfo['remark'] ?? '出库申请已提交',
            'detail_url' => $detailUrl
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'outapply', $data);
    }
}
