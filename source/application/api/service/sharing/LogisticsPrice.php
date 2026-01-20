<?php
namespace app\api\service\sharing;

use app\api\model\sharing\LinePriceTier;

/**
 * 集运拼团价格计算服务
 */
class LogisticsPrice
{
    /**
     * 根据总重量计算当前单价
     */
    public static function calculatePricePerKg($lineId, $totalWeight, $wxappId)
    {
        $tiers = LinePriceTier::where('line_id', $lineId)
            ->where('wxapp_id', $wxappId)
            ->order(['min_weight' => 'asc'])
            ->select();
        
        foreach ($tiers as $tier) {
            if ($totalWeight >= $tier['min_weight'] && 
                ($tier['max_weight'] === null || $totalWeight < $tier['max_weight'])) {
                return $tier['price_per_kg'];
            }
        }
        
        if (count($tiers) > 0) {
            return $tiers[0]['price_per_kg'];
        }
        return 0;
    }
    
    /**
     * 计算成员运费（按重量比例）
     */
    public static function calculateMemberFreight($memberWeight, $totalWeight, $pricePerKg)
    {
        if ($totalWeight == 0) {
            return 0;
        }
        return ($memberWeight / $totalWeight) * ($totalWeight * $pricePerKg);
    }
    
    /**
     * 获取下一价格阶梯信息
     */
    public static function getNextTier($lineId, $currentWeight, $currentPrice, $wxappId)
    {
        $nextTier = LinePriceTier::where('line_id', $lineId)
            ->where('wxapp_id', $wxappId)
            ->where('min_weight', '>', $currentWeight)
            ->order(['min_weight' => 'asc'])
            ->find();
        
        if ($nextTier) {
            return [
                'weight_target' => $nextTier['min_weight'],
                'price_per_kg' => $nextTier['price_per_kg'],
                'weight_needed' => $nextTier['min_weight'] - $currentWeight,
                'savings_per_kg' => $currentPrice - $nextTier['price_per_kg']
            ];
        }
        
        return null;
    }
}
