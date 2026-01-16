<?php

namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 到仓通知
 * Class Toshop
 * @package app\common\service\message\line
 */
class Toshop extends Basics
{
    protected $param = [];
    
    /**
     * 发送到仓通知
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
        $packageInfo = $this->param;
        $wxappId = $packageInfo['wxapp_id'];
        
        // 获取用户 LINE ID
        $lineUserId = $this->getLineUserIdByUserId($packageInfo['member_id']);
        if (empty($lineUserId)) {
            return false;
        }
        
        // 构建详情页链接
        $detailUrl = $this->buildLiffUrl(
            '/package/detail',
            [
                'id' => $packageInfo['id'] ?? 0,
                'rtype' => 10
            ],
            $wxappId
        );
        
        // 构建模板数据
        $data = [
            'express_company' => $packageInfo['express_company'] ?? '',
            'express_num' => $packageInfo['express_num'] ?? '',
            'arrive_time' => $packageInfo['arrive_time'] ?? date('Y-m-d H:i:s'),
            'remark' => $packageInfo['remark'] ?? '包裹已到仓',
            'detail_url' => $detailUrl
        ];
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'toshop', $data);
    }
}
