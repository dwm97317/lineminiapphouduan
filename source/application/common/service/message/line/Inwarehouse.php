<?php

namespace app\common\service\message\line;

/**
 * LINE 消息通知 - 包裹入库
 * Class Inwarehouse
 * @package app\common\service\message\line
 */
class Inwarehouse extends Basics
{
    protected $param = [];
    
    /**
     * 发送入库通知
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
            '/package/detail',
            [
                'id' => $orderInfo['id'] ?? 0,
                'rtype' => 10
            ],
            $wxappId
        );
        
        // 构建尺寸字符串（只有当长宽高都大于0时才显示）
        $sizeStr = '';
        if (!empty($orderInfo['length']) && $orderInfo['length'] > 0 && 
            !empty($orderInfo['width']) && $orderInfo['width'] > 0 && 
            !empty($orderInfo['height']) && $orderInfo['height'] > 0) {
            $sizeStr = $orderInfo['length'] . 'x' . $orderInfo['width'] . 'x' . $orderInfo['height'] . 'cm';
        }
        
        // 构建模板数据
        $data = [
            'shop_name' => !empty($orderInfo['shop_name']) ? $orderInfo['shop_name'] : '未知仓库',
            'express_num' => !empty($orderInfo['express_num']) ? $orderInfo['express_num'] : '无',
            'entering_warehouse_time' => !empty($orderInfo['entering_warehouse_time']) ? $orderInfo['entering_warehouse_time'] : date('Y-m-d H:i:s'),
            'weight' => isset($orderInfo['weight']) && $orderInfo['weight'] > 0 ? $orderInfo['weight'] : '待称重',
            'size' => $sizeStr, // 尺寸（为空时会被自动移除）
            'mark' => !empty($orderInfo['usermark']) ? $orderInfo['usermark'] : '', // 唛头（为空时会被自动移除）
            'remark' => !empty($orderInfo['remark']) ? $orderInfo['remark'] : '包裹已入库，可提交打包',
            'detail_url' => $detailUrl
        ];
        
        // 添加图片数据（如果有）
        if (!empty($orderInfo['images'])) {
            $data['images'] = $orderInfo['images'];
        } elseif (!empty($orderInfo['packageimage'])) {
            $data['packageimage'] = $orderInfo['packageimage'];
        }
        
        return $this->sendLineFlexMsg($wxappId, $lineUserId, 'inwarehouse', $data);
    }
}
