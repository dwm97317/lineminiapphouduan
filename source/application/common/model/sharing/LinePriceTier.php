<?php

namespace app\common\model\sharing;

use app\common\model\BaseModel;

/**
 * 线路价格阶梯模型
 */
class LinePriceTier extends BaseModel
{
    protected $name = 'line_price_tier';
    protected $updateTime = 'update_time';
    
    /**
     * 获取指定线路的价格阶梯
     */
    public static function getByLineId($lineId, $wxappId)
    {
        return self::where('line_id', '=', $lineId)
            ->where('wxapp_id', '=', $wxappId)
            ->order('min_weight', 'asc')
            ->select();
    }
    
    /**
     * 根据重量计算价格
     */
    public static function calculatePrice($lineId, $weight, $wxappId)
    {
        $tiers = self::getByLineId($lineId, $wxappId);
        
        if ($tiers->isEmpty()) {
            // 如果没有配置，返回默认价格
            return 100;
        }
        
        $currentPrice = $tiers[0]['price_per_kg'];
        
        foreach ($tiers as $tier) {
            if ($weight >= $tier['min_weight']) {
                $currentPrice = $tier['price_per_kg'];
            } else {
                break;
            }
        }
        
        return $currentPrice;
    }
}
