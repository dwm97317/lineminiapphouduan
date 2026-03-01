<?php

namespace app\store\service\statement;

/**
 * 公式计算器
 * 简单实现版本（使用安全的字符串替换 + eval）
 * TODO: 生产环境建议使用 Symfony Expression Language
 */
class FormulaCalculator
{
    /**
     * 计算公式
     * @param string $formula 公式（如：{weight} * 46 + 10）
     * @param float $weight 订单重量
     * @return float 计算结果
     */
    public function calculate($formula, $weight)
    {
        try {
            // 替换变量
            $expression = str_replace('{weight}', $weight, $formula);
            
            // 验证只包含安全字符
            if (!preg_match('/^[\d\.\+\-\*\/\(\)\s]+$/', $expression)) {
                throw new \Exception('公式包含非法字符');
            }
            
            // 计算
            $result = eval("return $expression;");
            
            // 保留2位小数
            return round($result, 2);
            
        } catch (\Exception $e) {
            throw new \Exception('公式计算失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 验证公式语法
     * @param string $formula 公式
     * @return bool 是否有效
     */
    public function validate($formula)
    {
        try {
            // 替换变量为测试值
            $expression = str_replace('{weight}', '10', $formula);
            
            // 验证只包含安全字符
            if (!preg_match('/^[\d\.\+\-\*\/\(\)\s]+$/', $expression)) {
                return false;
            }
            
            // 尝试计算
            $result = eval("return $expression;");
            
            return is_numeric($result);
            
        } catch (\Exception $e) {
            return false;
        }
    }
    
    /**
     * 测试公式（使用测试重量）
     * @param string $formula 公式
     * @param float $testWeight 测试重量（默认10KG）
     * @return array ['valid' => true, 'result' => 470.00, 'error' => null]
     */
    public function test($formula, $testWeight = 10)
    {
        try {
            if (!$this->validate($formula)) {
                return [
                    'valid' => false,
                    'result' => null,
                    'error' => '公式语法错误'
                ];
            }
            
            $result = $this->calculate($formula, $testWeight);
            
            if ($result <= 0) {
                return [
                    'valid' => false,
                    'result' => $result,
                    'error' => '公式计算结果必须大于0'
                ];
            }
            
            return [
                'valid' => true,
                'result' => $result,
                'error' => null
            ];
            
        } catch (\Exception $e) {
            return [
                'valid' => false,
                'result' => null,
                'error' => $e->getMessage()
            ];
        }
    }
}
