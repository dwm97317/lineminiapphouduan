<?php

namespace app\store\model\sharing;

use app\common\model\sharing\LinePriceTier as LinePriceTierModel;

/**
 * 线路价格阶梯模型（Store）
 */
class LinePriceTier extends LinePriceTierModel
{
    /**
     * 批量保存价格阶梯
     */
    public function saveBatch($lineId, $tiers)
    {
        $this->startTrans();
        try {
            // 删除该线路的旧配置
            self::where('line_id', '=', $lineId)
                ->where('wxapp_id', '=', self::$wxapp_id)
                ->delete();
            
            // 插入新配置
            $data = [];
            $sort = 1;
            foreach ($tiers as $tier) {
                if (empty($tier['min_weight']) && $tier['min_weight'] !== '0') {
                    continue;
                }
                
                $data[] = [
                    'line_id' => $lineId,
                    'min_weight' => $tier['min_weight'],
                    'price_per_kg' => $tier['price_per_kg'],
                    'tier_name' => $tier['tier_name'] ?? '',
                    'sort' => $sort++,
                    'wxapp_id' => self::$wxapp_id,
                    'create_time' => time(),
                    'update_time' => time()
                ];
            }
            
            if (!empty($data)) {
                $this->saveAll($data);
            }
            
            $this->commit();
            return true;
        } catch (\Exception $e) {
            $this->rollback();
            $this->error = $e->getMessage();
            return false;
        }
    }
    
    /**
     * 获取所有线路的配置统计
     */
    public static function getConfigStats()
    {
        // 获取所有线路
        $allLines = \app\common\model\Line::where('wxapp_id', '=', self::$wxapp_id)
            ->where('status', '=', 1)
            ->column('id');
        
        // 获取已配置的线路
        $configuredLines = self::where('wxapp_id', '=', self::$wxapp_id)
            ->group('line_id')
            ->column('line_id');
        
        return [
            'total' => count($allLines),
            'configured' => count($configuredLines),
            'unconfigured' => count($allLines) - count($configuredLines)
        ];
    }
}
