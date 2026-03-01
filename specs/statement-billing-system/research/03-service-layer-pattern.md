# 服务层架构模式研究

## 概述

服务层模式将业务逻辑从控制器中分离出来，提高代码的可维护性和可测试性。

## 为什么需要服务层？

### 当前问题

`Package/Index.php` 已有1457行代码，存在以下问题：
- 控制器承担过多职责
- 业务逻辑难以复用
- 难以进行单元测试
- 代码耦合度高

### 服务层的优势

- ✅ 职责分离：控制器只负责请求响应
- ✅ 业务逻辑复用：服务可以被多个控制器调用
- ✅ 易于测试：服务层可以独立测试
- ✅ 代码组织清晰：按业务功能划分

## ThinkPHP 5.0 中的服务层

### 1. 目录结构

```
source/application/store/
├── controller/
│   ├── package/
│   │   ├── Index.php          (薄控制器)
│   │   └── Statement.php      (薄控制器)
│   └── finance/
│       └── Config.php         (薄控制器)
├── service/
│   ├── statement/
│   │   ├── StatementService.php       (账单生成服务)
│   │   ├── StatementManageService.php (账单管理服务)
│   │   └── PriceCalculator.php        (计价引擎)
│   └── finance/
│       └── FinanceConfigService.php   (财务配置服务)
└── model/
    ├── Statement.php          (数据模型)
    └── Package.php            (数据模型)
```

### 2. 命名空间

```php
// 服务层命名空间
namespace app\store\service\statement;

// 控制器命名空间
namespace app\store\controller\package;

// 模型命名空间
namespace app\store\model;
```

## 实现示例

### 1. 薄控制器

```php
<?php
namespace app\store\controller\package;

use app\store\controller\Controller;
use app\store\service\statement\StatementService;

class Statement extends Controller
{
    private $service;
    
    public function __construct()
    {
        parent::__construct();
        $this->service = new StatementService();
    }
    
    /**
     * 生成账单
     */
    public function create()
    {
        $packageIds = $this->request->post('package_ids/a');
        $memberId = $this->request->post('member_id');
        
        try {
            $statement = $this->service->createStatement($packageIds, $memberId);
            return $this->renderSuccess('账单生成成功', [
                'statement_id' => $statement['id']
            ]);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
    
    /**
     * 账单详情
     */
    public function detail()
    {
        $statementId = $this->request->param('statement_id');
        
        try {
            $data = $this->service->getStatementDetail($statementId);
            return $this->renderSuccess('', $data);
        } catch (\Exception $e) {
            return $this->renderError($e->getMessage());
        }
    }
}
```

**控制器职责**：
- 接收HTTP请求
- 验证基本参数
- 调用服务层
- 返回HTTP响应
- 不包含业务逻辑

### 2. 服务层

```php
<?php
namespace app\store\service\statement;

use app\store\model\Statement;
use app\store\model\Package;
use app\store\model\FinanceConfig;
use app\common\service\ExcelService;
use think\Db;

class StatementService
{
    private $wxapp_id;
    
    public function __construct()
    {
        $this->wxapp_id = helper::getWxappId();
    }
    
    /**
     * 创建账单
     */
    public function createStatement($packageIds, $memberId)
    {
        // 1. 验证和锁定订单
        $packages = $this->validateAndLockPackages($packageIds, $memberId);
        
        // 2. 获取计价配置
        $priceConfig = FinanceConfig::getEffectivePrice($memberId);
        
        // 3. 计算金额
        $calculator = new PriceCalculator();
        $packages = $calculator->calculate($packages, $priceConfig);
        
        // 4. 创建账单记录
        Db::startTrans();
        try {
            $statement = $this->createStatementRecord($packages, $memberId);
            $this->bindPackages($packageIds, $statement['id']);
            Db::commit();
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
        
        // 5. 生成Excel（事务外）
        $this->generateExcel($statement, $packages);
        
        return $statement;
    }
    
    /**
     * 验证和锁定订单
     */
    private function validateAndLockPackages($packageIds, $memberId)
    {
        $packages = Package::where('id', 'in', $packageIds)
            ->where('member_id', $memberId)
            ->where('statement_id', null)
            ->where('is_delete', 0)
            ->where('wxapp_id', $this->wxapp_id)
            ->lock(true)
            ->select();
        
        if (count($packages) != count($packageIds)) {
            $lockedIds = array_column($packages, 'id');
            $unavailableIds = array_diff($packageIds, $lockedIds);
            throw new \Exception('订单 ' . implode(',', $unavailableIds) . ' 不可用或已归档');
        }
        
        return $packages;
    }
    
    /**
     * 创建账单记录
     */
    private function createStatementRecord($packages, $memberId)
    {
        $totalWeight = array_sum(array_column($packages, 'weight'));
        $totalAmount = array_sum(array_column($packages, 'amount'));
        
        return Statement::create([
            'statement_no' => Statement::generateStatementNo(),
            'member_id' => $memberId,
            'member_name' => $packages[0]['member_name'],
            'start_date' => date('Y-m-01'),
            'end_date' => date('Y-m-t'),
            'total_packages' => count($packages),
            'total_weight' => $totalWeight,
            'total_amount' => $totalAmount,
            'status' => Statement::STATUS_DRAFT,
            'pay_status' => Statement::PAY_STATUS_UNPAID,
            'wxapp_id' => $this->wxapp_id
        ]);
    }
    
    /**
     * 绑定订单
     */
    private function bindPackages($packageIds, $statementId)
    {
        Package::where('id', 'in', $packageIds)
            ->update(['statement_id' => $statementId]);
    }
    
    /**
     * 生成Excel
     */
    private function generateExcel($statement, $packages)
    {
        $template = FinanceConfig::getDefaultTemplate();
        $excelService = new ExcelService();
        $excelPath = $excelService->generateStatementExcel($statement, $packages, $template);
        
        // 更新Excel路径
        Statement::where('id', $statement['id'])
            ->update(['excel_path' => $excelPath]);
    }
}
```

**服务层职责**：
- 业务逻辑处理
- 数据验证
- 事务管理
- 调用其他服务
- 不处理HTTP请求

### 3. 计价引擎（独立服务）

```php
<?php
namespace app\store\service\statement;

use app\store\model\FinanceConfig;

class PriceCalculator
{
    /**
     * 计算订单金额
     */
    public function calculate($packages, $priceConfig)
    {
        $priceType = $priceConfig['price_type'];
        
        foreach ($packages as &$package) {
            $unitPrice = $this->calculateUnitPrice($package, $priceConfig);
            $package['unit_price'] = $unitPrice;
            $package['amount'] = $package['weight'] * $unitPrice;
        }
        
        return $packages;
    }
    
    /**
     * 计算单价
     */
    private function calculateUnitPrice($package, $priceConfig)
    {
        switch ($priceConfig['price_type']) {
            case FinanceConfig::PRICE_TYPE_FIXED:
                return $this->calculateFixedPrice($priceConfig);
                
            case FinanceConfig::PRICE_TYPE_TIER:
                return $this->calculateTierPrice($package, $priceConfig);
                
            case FinanceConfig::PRICE_TYPE_LINE:
                return $this->calculateLinePrice($package, $priceConfig);
                
            case FinanceConfig::PRICE_TYPE_RANGE:
                return $this->calculateRangePrice($package, $priceConfig);
                
            case FinanceConfig::PRICE_TYPE_FORMULA:
                return $this->calculateFormulaPrice($package, $priceConfig);
                
            default:
                return 46.00; // 兜底价格
        }
    }
    
    /**
     * 固定单价
     */
    private function calculateFixedPrice($priceConfig)
    {
        return $priceConfig['unit_price'];
    }
    
    /**
     * 阶梯价格
     */
    private function calculateTierPrice($package, $priceConfig)
    {
        $tiers = json_decode($priceConfig['price_tier_json'], true);
        $weight = $package['weight'];
        
        foreach ($tiers['tiers'] as $tier) {
            if ($weight >= $tier['min'] && 
                ($tier['max'] === null || $weight < $tier['max'])) {
                return $tier['price'];
            }
        }
        
        return $priceConfig['unit_price'] ?? 46.00;
    }
    
    // ... 其他计价方式
}
```

## 服务层最佳实践

### 1. 单一职责

每个服务类只负责一个业务领域：

```php
// ✅ 正确：职责单一
StatementService         // 账单生成
StatementManageService   // 账单管理
PriceCalculator          // 计价引擎
ExcelService             // Excel生成

// ❌ 错误：职责混乱
StatementService         // 包含所有账单相关功能
```

### 2. 依赖注入

```php
class StatementService
{
    private $priceCalculator;
    private $excelService;
    
    public function __construct(
        PriceCalculator $priceCalculator = null,
        ExcelService $excelService = null
    ) {
        $this->priceCalculator = $priceCalculator ?: new PriceCalculator();
        $this->excelService = $excelService ?: new ExcelService();
    }
}
```

### 3. 返回值规范

```php
// ✅ 返回数据对象
public function getStatementDetail($statementId)
{
    return [
        'statement' => $statement,
        'packages' => $packages,
        'statistics' => $statistics
    ];
}

// ❌ 直接返回HTTP响应
public function getStatementDetail($statementId)
{
    return json(['code' => 1, 'data' => $data]);
}
```

### 4. 异常处理

```php
// 服务层抛出异常
public function createStatement($packageIds, $memberId)
{
    if (empty($packageIds)) {
        throw new \Exception('请选择订单');
    }
    
    // ... 业务逻辑
}

// 控制器捕获异常
public function create()
{
    try {
        $statement = $this->service->createStatement($packageIds, $memberId);
        return $this->renderSuccess('成功', $statement);
    } catch (\Exception $e) {
        return $this->renderError($e->getMessage());
    }
}
```

## 测试示例

服务层可以独立测试：

```php
class StatementServiceTest extends TestCase
{
    public function testCreateStatement()
    {
        $service = new StatementService();
        
        $packageIds = [1, 2, 3];
        $memberId = 31398;
        
        $statement = $service->createStatement($packageIds, $memberId);
        
        $this->assertNotNull($statement);
        $this->assertEquals(3, $statement['total_packages']);
    }
}
```

## 结论

服务层模式非常适合账单系统：
- ✅ 解决控制器过大问题
- ✅ 业务逻辑清晰可复用
- ✅ 易于测试和维护
- ✅ ThinkPHP 5.0 完全支持
- ⚠️ 需要合理划分服务职责
- ⚠️ 避免服务层过度设计

**推荐使用**，并遵循以下原则：
1. 控制器薄，服务层厚
2. 一个服务类一个职责
3. 服务层不处理HTTP
4. 通过异常传递错误
