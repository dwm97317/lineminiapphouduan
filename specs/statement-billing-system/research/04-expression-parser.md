# 表达式解析研究

## 概述

自定义公式是5种计价方式之一，需要安全地解析和执行用户输入的数学表达式。

## 需求回顾

- 可用变量：`{weight}` - 订单重量
- 支持简单数学运算：`+`, `-`, `*`, `/`, `(`, `)`
- 示例公式：`{weight} * 46 + 10`
- 不支持复杂条件判断和循环

## 方案对比

### 方案1: eval() - ❌ 不推荐

```php
$formula = '{weight} * 46 + 10';
$weight = 15.5;

// 替换变量
$expression = str_replace('{weight}', $weight, $formula);

// 执行
$result = eval("return $expression;");
```

**优点**：
- 实现简单
- 支持所有PHP表达式

**缺点**：
- ⚠️ 严重安全风险（可执行任意PHP代码）
- ⚠️ 难以调试
- ⚠️ 无法验证公式合法性

**结论**：不推荐使用

### 方案2: Symfony Expression Language - ✅ 推荐

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

$expressionLanguage = new ExpressionLanguage();

$formula = 'weight * 46 + 10';
$result = $expressionLanguage->evaluate($formula, [
    'weight' => 15.5
]);
```

**优点**：
- ✅ 安全（沙箱环境）
- ✅ 支持数学运算
- ✅ 可以验证语法
- ✅ 性能好（可缓存编译结果）

**缺点**：
- 需要Composer安装
- 学习成本

**安装**：
```bash
composer require symfony/expression-language
```

### 方案3: 正则表达式 + 手动解析 - ⚠️ 备选

```php
class SimpleCalculator
{
    public function calculate($formula, $variables)
    {
        // 1. 替换变量
        foreach ($variables as $key => $value) {
            $formula = str_replace('{' . $key . '}', $value, $formula);
        }
        
        // 2. 验证只包含数字和运算符
        if (!preg_match('/^[\d\.\+\-\*\/\(\)\s]+$/', $formula)) {
            throw new \Exception('公式包含非法字符');
        }
        
        // 3. 使用bc_math计算（避免eval）
        return $this->evaluateSafe($formula);
    }
    
    private function evaluateSafe($expression)
    {
        // 简单的递归下降解析器
        // 实现较复杂，此处省略
    }
}
```

**优点**：
- 无需外部依赖
- 完全可控

**缺点**：
- 实现复杂
- 容易出错
- 维护成本高

## 推荐方案：Symfony Expression Language

### 1. 安装

```bash
composer require symfony/expression-language
```

### 2. 基本使用

```php
<?php
namespace app\store\service\statement;

use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class FormulaCalculator
{
    private $expressionLanguage;
    
    public function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();
    }
    
    /**
     * 计算公式
     */
    public function calculate($formula, $weight)
    {
        try {
            // 移除花括号（{weight} -> weight）
            $expression = str_replace(['{', '}'], '', $formula);
            
            // 计算
            $result = $this->expressionLanguage->evaluate($expression, [
                'weight' => $weight
            ]);
            
            return round($result, 2);
            
        } catch (\Exception $e) {
            throw new \Exception('公式计算失败: ' . $e->getMessage());
        }
    }
    
    /**
     * 验证公式语法
     */
    public function validate($formula)
    {
        try {
            $expression = str_replace(['{', '}'], '', $formula);
            
            // 尝试编译（不执行）
            $this->expressionLanguage->parse($expression, ['weight']);
            
            return true;
            
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

### 3. 在计价引擎中使用

```php
class PriceCalculator
{
    private $formulaCalculator;
    
    public function __construct()
    {
        $this->formulaCalculator = new FormulaCalculator();
    }
    
    /**
     * 自定义公式计价
     */
    private function calculateFormulaPrice($package, $priceConfig)
    {
        $formula = $priceConfig['price_formula'];
        $weight = $package['weight'];
        
        try {
            return $this->formulaCalculator->calculate($formula, $weight);
        } catch (\Exception $e) {
            // 公式错误，使用兜底价格
            \think\Log::error('公式计算失败: ' . $e->getMessage(), [
                'formula' => $formula,
                'weight' => $weight
            ]);
            
            return 46.00;
        }
    }
}
```

### 4. 公式验证（保存配置时）

```php
class FinanceConfigService
{
    public function saveConfig($data)
    {
        // 如果是自定义公式，验证语法
        if ($data['price_type'] == FinanceConfig::PRICE_TYPE_FORMULA) {
            $calculator = new FormulaCalculator();
            
            if (!$calculator->validate($data['price_formula'])) {
                throw new \Exception('公式语法错误，请检查');
            }
            
            // 测试计算
            try {
                $testResult = $calculator->calculate($data['price_formula'], 10);
                if ($testResult <= 0) {
                    throw new \Exception('公式计算结果必须大于0');
                }
            } catch (\Exception $e) {
                throw new \Exception('公式测试失败: ' . $e->getMessage());
            }
        }
        
        // 保存配置
        FinanceConfig::create($data);
    }
}
```

## 支持的运算符

Symfony Expression Language 支持：

```php
// 算术运算
weight * 46          // 乘法
weight + 10          // 加法
weight - 5           // 减法
weight / 2           // 除法
weight % 3           // 取模

// 括号
(weight + 10) * 46

// 比较运算（如果需要）
weight > 10 ? 50 : 46

// 逻辑运算（如果需要）
weight > 10 and weight < 50
```

## 安全性

### 1. 沙箱环境

Symfony Expression Language 在沙箱中执行，无法：
- 调用PHP函数
- 访问文件系统
- 执行系统命令
- 访问全局变量

### 2. 变量白名单

```php
// 只允许使用 weight 变量
$this->expressionLanguage->evaluate($expression, [
    'weight' => $weight
]);

// 如果公式中使用了未定义的变量，会抛出异常
```

### 3. 语法验证

```php
// 保存前验证
if (!$calculator->validate($formula)) {
    throw new \Exception('公式语法错误');
}
```

## 性能优化

### 1. 缓存编译结果

```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

$cache = new FilesystemAdapter();
$expressionLanguage = new ExpressionLanguage($cache);

// 第一次编译并缓存
$result = $expressionLanguage->evaluate($expression, ['weight' => 10]);

// 后续使用缓存
$result = $expressionLanguage->evaluate($expression, ['weight' => 20]);
```

### 2. 预编译

```php
// 编译一次
$compiled = $expressionLanguage->compile($expression, ['weight']);

// 多次使用
foreach ($packages as $package) {
    $result = eval("return $compiled;");
}
```

## 错误处理

```php
try {
    $result = $calculator->calculate($formula, $weight);
} catch (\Symfony\Component\ExpressionLanguage\SyntaxError $e) {
    // 语法错误
    throw new \Exception('公式语法错误: ' . $e->getMessage());
} catch (\Exception $e) {
    // 其他错误（如除以0）
    throw new \Exception('公式计算失败: ' . $e->getMessage());
}
```

## 用户界面提示

在前端配置页面提供公式帮助：

```html
<div class="formula-help">
    <h4>公式说明</h4>
    <ul>
        <li>可用变量：{weight} - 订单重量（KG）</li>
        <li>支持运算符：+ - * / ( )</li>
        <li>示例：{weight} * 46 + 10</li>
        <li>示例：({weight} + 5) * 42</li>
    </ul>
</div>
```

## 测试用例

```php
class FormulaCalculatorTest extends TestCase
{
    private $calculator;
    
    public function setUp()
    {
        $this->calculator = new FormulaCalculator();
    }
    
    public function testSimpleMultiplication()
    {
        $result = $this->calculator->calculate('{weight} * 46', 10);
        $this->assertEquals(460, $result);
    }
    
    public function testComplexFormula()
    {
        $result = $this->calculator->calculate('({weight} + 5) * 42', 10);
        $this->assertEquals(630, $result);
    }
    
    public function testInvalidFormula()
    {
        $this->expectException(\Exception::class);
        $this->calculator->calculate('{weight} * * 46', 10);
    }
}
```

## 结论

**推荐使用 Symfony Expression Language**：
- ✅ 安全可靠（沙箱环境）
- ✅ 功能完善（支持所有数学运算）
- ✅ 性能优秀（可缓存）
- ✅ 易于使用和维护
- ⚠️ 需要Composer安装
- ⚠️ 增加约50KB依赖

**备选方案**：如果无法使用Composer，可以实现简单的正则验证 + bc_math计算，但安全性和功能性会降低。

**实施建议**：
1. 使用Symfony Expression Language
2. 保存配置时验证公式语法
3. 计算时捕获异常并使用兜底价格
4. 前端提供公式帮助和实时验证
