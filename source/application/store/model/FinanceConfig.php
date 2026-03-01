<?php

namespace app\store\model;

use think\Model;

/**
 * 财务配置模型
 */
class FinanceConfig extends Model
{
    protected $name = 'finance_config';
    // 关闭自动时间戳
    protected $autoWriteTimestamp = false;
    protected $createTime = false;
    protected $updateTime = false;
    
    // 计价方式常量
    const PRICE_TYPE_FIXED = 1;    // 固定单价
    const PRICE_TYPE_TIER = 2;     // 阶梯价格
    const PRICE_TYPE_LINE = 3;     // 线路价格
    const PRICE_TYPE_RANGE = 4;    // 区间价格
    const PRICE_TYPE_FORMULA = 5;  // 自定义公式
    
    // 状态常量
    const STATUS_DISABLED = 0;  // 禁用
    const STATUS_ENABLED = 1;   // 启用
    
    /**
     * 获取有效配置（优先级：客户专属 > 历史单价 > 全局默认 > 系统兜底）
     * @param int $memberId 客户ID
     * @return array|null
     */
    public static function getEffectivePrice($memberId)
    {
        $wxappId = self::getWxappId();
        
        // 需要查询的字段（避免timestamp字段）
        $fields = 'id,member_id,price_type,unit_price,price_tier_json,price_line_json,price_range_json,price_formula,status,wxapp_id';
        
        // 1. 查找客户专属配置（status=1）
        $customerConfig = self::field($fields)
            ->where('member_id', $memberId)
            ->where('wxapp_id', $wxappId)
            ->where('status', self::STATUS_ENABLED)
            ->find();
        
        if ($customerConfig) {
            return $customerConfig->toArray();
        }
        
        // 2. 查找历史单价表
        $historyPrice = HistoryPrice::getMemberPrice($memberId);
        if ($historyPrice) {
            return [
                'price_type' => self::PRICE_TYPE_FIXED,
                'unit_price' => $historyPrice,
                'source' => 'history'
            ];
        }
        
        // 3. 查找全局默认配置
        $globalConfig = self::field($fields)
            ->where('member_id', null)
            ->where('wxapp_id', $wxappId)
            ->where('status', self::STATUS_ENABLED)
            ->find();
        
        if ($globalConfig) {
            return $globalConfig->toArray();
        }
        
        // 4. 系统兜底（46元/KG）
        return [
            'price_type' => self::PRICE_TYPE_FIXED,
            'unit_price' => 46.00,
            'source' => 'fallback'
        ];
    }
    
    /**
     * 获取当前小程序ID
     */
    private static function getWxappId()
    {
        // 从Session获取wxapp_id（store模块）
        $session = \think\Session::get('yoshop_store');
        return $session['wxapp']['wxapp_id'] ?? 10001;
    }
    
    /**
     * JSON字段获取器
     */
    public function getPriceTierJsonAttr($value)
    {
        return $value ? json_decode($value, true) : null;
    }
    
    public function getPriceLineJsonAttr($value)
    {
        return $value ? json_decode($value, true) : null;
    }
    
    public function getPriceRangeJsonAttr($value)
    {
        return $value ? json_decode($value, true) : null;
    }
    
    /**
     * JSON字段修改器
     */
    public function setPriceTierJsonAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }
    
    public function setPriceLineJsonAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }
    
    public function setPriceRangeJsonAttr($value)
    {
        return is_array($value) ? json_encode($value) : $value;
    }
}
