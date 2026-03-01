<?php

namespace app\store\service\statement;

use app\store\model\FinanceConfig;

/**
 * 计价引擎
 * 根据配置计算订单金额
 */
class PriceCalculator
{
    private $formulaCalculator;
    
    public function __construct()
    {
        $this->formulaCalculator = new FormulaCalculator();
    }
    
    /**
     * 批量计算订单金额
     * @param array $inpacks 集运订单列表
     * @param array $priceConfig 计价配置
     * @return array 计算后的订单列表（添加unit_price和calculated_amount字段）
     */
    public function calculate($inpacks, $priceConfig)
    {
        $result = [];
        
        try {
            // 记录输入参数
            \think\Log::info('PriceCalculator::calculate 开始: count=' . count($inpacks) . ', type=' . ($priceConfig['price_type'] ?? 'unknown'));
            
            foreach ($inpacks as $index => $inpack) {
                try {
                    // 转换为数组（如果是对象）
                    $inpackArray = is_array($inpack) ? $inpack : (array)$inpack;
                    
                    // 使用计费重量（cale_weight）
                    $weight = floatval($inpackArray['cale_weight'] ?? $inpackArray['weight'] ?? 0);
                    
                    if ($weight <= 0) {
                        // 如果没有计费重量，跳过
                        \think\Log::warning('集运订单重量为0，跳过: id=' . ($inpackArray['id'] ?? 'unknown'));
                        continue;
                    }
                    
                    $unitPrice = $this->calculateUnitPrice($inpackArray, $priceConfig);
                    $inpackArray['unit_price'] = $unitPrice;
                    $inpackArray['calculated_amount'] = round($weight * $unitPrice, 2);
                    
                    $result[] = $inpackArray;
                    
                } catch (\Exception $e) {
                    \think\Log::error('单个集运订单计算失败: index=' . $index . ', id=' . ($inpackArray['id'] ?? 'unknown') . ', error=' . $e->getMessage());
                    throw $e;
                }
            }
            
            \think\Log::info('PriceCalculator::calculate 完成: count=' . count($result));
            
            return $result;
            
        } catch (\Exception $e) {
            \think\Log::error('计价计算失败: ' . $e->getMessage() . ' at ' . $e->getFile() . ':' . $e->getLine());
            throw new \Exception('计价计算失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 计算单个订单的单价
     * @param array $inpack 集运订单数据
     * @param array $priceConfig 计价配置
     * @return float 单价
     */
    private function calculateUnitPrice($inpack, $priceConfig)
    {
        try {
            $priceType = $priceConfig['price_type'] ?? FinanceConfig::PRICE_TYPE_FIXED;
            
            \think\Log::info('calculateUnitPrice: type=' . $priceType . ', id=' . ($inpack['id'] ?? 'unknown'));
            
            switch ($priceType) {
                case FinanceConfig::PRICE_TYPE_FIXED:
                    return $this->calculateFixedPrice($priceConfig);
                    
                case FinanceConfig::PRICE_TYPE_TIER:
                    return $this->calculateTierPrice($inpack, $priceConfig);
                    
                case FinanceConfig::PRICE_TYPE_LINE:
                    return $this->calculateLinePrice($inpack, $priceConfig);
                    
                case FinanceConfig::PRICE_TYPE_RANGE:
                    return $this->calculateRangePrice($inpack, $priceConfig);
                    
                case FinanceConfig::PRICE_TYPE_FORMULA:
                    return $this->calculateFormulaPrice($inpack, $priceConfig);
                    
                default:
                    // 兜底价格
                    return 46.00;
            }
        } catch (\Exception $e) {
            \think\Log::error('calculateUnitPrice 失败: id=' . ($inpack['id'] ?? 'unknown') . ', type=' . ($priceConfig['price_type'] ?? 'unknown') . ', error=' . $e->getMessage());
            throw $e;
        }
    }
    
    /**
     * 固定单价
     */
    private function calculateFixedPrice($priceConfig)
    {
        return floatval($priceConfig['unit_price'] ?? 46.00);
    }
    
    /**
     * 阶梯价格（每个订单单独计算）
     * 配置格式: {"tiers": [{"min": 0, "max": 10, "price": 50}, {"min": 10, "max": null, "price": 46}]}
     */
    private function calculateTierPrice($inpack, $priceConfig)
    {
        // 使用计费重量
        $weight = floatval($inpack['cale_weight'] ?? $inpack['weight'] ?? 0);
        $tierConfig = $priceConfig['price_tier_json'] ?? null;
        
        // 防御性检查：确保tierConfig是数组
        if (!is_array($tierConfig) || !isset($tierConfig['tiers']) || !is_array($tierConfig['tiers'])) {
            return floatval($priceConfig['unit_price'] ?? 46.00);
        }
        
        $tiers = $tierConfig['tiers'];
        
        // 按min排序 - 使用太空船操作符确保返回正确的整数
        usort($tiers, function($a, $b) {
            if (!is_array($a) || !is_array($b)) {
                return 0;
            }
            $aMin = floatval($a['min'] ?? 0);
            $bMin = floatval($b['min'] ?? 0);
            return $aMin <=> $bMin;  // PHP 7+ 太空船操作符，返回 -1, 0, 或 1
        });
        
        // 查找匹配的阶梯
        foreach ($tiers as $tier) {
            if (!is_array($tier)) continue;
            
            $min = floatval($tier['min'] ?? 0);
            $max = ($tier['max'] ?? null) === null ? PHP_FLOAT_MAX : floatval($tier['max']);
            
            if ($weight >= $min && $weight < $max) {
                return floatval($tier['price'] ?? 46.00);
            }
        }
        
        // 如果没有匹配，使用最后一个阶梯的价格
        if (!empty($tiers)) {
            $lastTier = $tiers[count($tiers) - 1];
            if (is_array($lastTier)) {
                return floatval($lastTier['price'] ?? 46.00);
            }
        }
        
        return floatval($priceConfig['unit_price'] ?? 46.00);
    }
    
    /**
     * 线路价格
     * 配置格式: {"lines": [{"line_id": 1, "price": 48}, {"line_id": 2, "price": 50}]}
     */
    private function calculateLinePrice($package, $priceConfig)
    {
        $lineId = intval($package['line_id'] ?? 0);
        $lineConfig = $priceConfig['price_line_json'] ?? null;
        
        // 防御性检查：确保lineConfig是数组
        if (!is_array($lineConfig) || !isset($lineConfig['lines']) || !is_array($lineConfig['lines'])) {
            return floatval($priceConfig['unit_price'] ?? 46.00);
        }
        
        $lines = $lineConfig['lines'];
        
        // 查找匹配的线路
        foreach ($lines as $line) {
            if (!is_array($line)) continue;
            
            if (intval($line['line_id'] ?? 0) == $lineId) {
                return floatval($line['price'] ?? 46.00);
            }
        }
        
        // 没有匹配，使用默认单价
        return floatval($priceConfig['unit_price'] ?? 46.00);
    }
    
    /**
     * 区间价格（按日期区间）
     * 配置格式: {"ranges": [{"start_date": "2026-01-01", "end_date": "2026-01-31", "price": 48}]}
     */
    private function calculateRangePrice($package, $priceConfig)
    {
        $packageDate = $package['create_time'] ?? date('Y-m-d');
        if (is_numeric($packageDate)) {
            $packageDate = date('Y-m-d', $packageDate);
        }
        
        $rangeConfig = $priceConfig['price_range_json'] ?? null;
        
        // 防御性检查：确保rangeConfig是数组
        if (!is_array($rangeConfig) || !isset($rangeConfig['ranges']) || !is_array($rangeConfig['ranges'])) {
            return floatval($priceConfig['unit_price'] ?? 46.00);
        }
        
        $ranges = $rangeConfig['ranges'];
        
        // 查找匹配的区间
        foreach ($ranges as $range) {
            if (!is_array($range)) continue;
            
            $startDate = $range['start_date'] ?? '';
            $endDate = $range['end_date'] ?? '';
            
            if ($packageDate >= $startDate && $packageDate <= $endDate) {
                return floatval($range['price'] ?? 46.00);
            }
        }
        
        // 没有匹配，使用默认单价
        return floatval($priceConfig['unit_price'] ?? 46.00);
    }
    
    /**
     * 自定义公式
     * 配置格式: "{weight} * 46 + 10"
     */
    private function calculateFormulaPrice($inpack, $priceConfig)
    {
        $formula = $priceConfig['price_formula'] ?? null;
        
        if (!$formula) {
            return floatval($priceConfig['unit_price'] ?? 46.00);
        }
        
        try {
            // 使用计费重量
            $weight = floatval($inpack['cale_weight'] ?? $inpack['weight'] ?? 0);
            return $this->formulaCalculator->calculate($formula, $weight);
            
        } catch (\Exception $e) {
            // 公式错误，使用兜底价格
            \think\Log::error('公式计算失败: formula=' . $formula . ', weight=' . ($inpack['cale_weight'] ?? $inpack['weight'] ?? 0) . ', id=' . ($inpack['id'] ?? 'unknown') . ', error=' . $e->getMessage());
            
            return 46.00;
        }
    }
}
