# 账单系统详细设计文档

**项目名称**：集运订单账单系统  
**版本**：1.0  
**创建日期**：2026-02-28  
**状态**：设计阶段

---

## 一、概述

### 1.1 项目背景

现有集运系统的订单管理页面（Package/Index.php）已达1457行代码，维护困难。需要新增账单功能，为客户生成对账单，支持多种计价方式和Excel导出。

### 1.2 设计目标

- 不修改现有Package/Index.php代码
- 使用服务层模式，提高代码可维护性
- 支持5种灵活的计价方式
- 保证数据一致性和并发安全
- 提供友好的用户界面

### 1.3 核心功能

1. **财务配置管理** - 支持5种计价方式配置
2. **账单生成** - 选择订单生成账单，自动计算金额
3. **账单管理** - 查看、支付、作废账单
4. **Excel导出** - 生成专业的账单Excel文件
5. **历史单价导入** - 批量导入历史价格数据

---

## 二、详细需求

### 2.1 账单生成流程

1. 财务人员手动选择同一客户的订单
2. 系统根据计价配置自动计算金额
3. 生成账单记录并绑定订单
4. 生成Excel文件供下载

### 2.2 计价方式优先级

```
客户专属配置（status=1）
    ↓ (不存在或禁用)
历史单价表
    ↓ (不存在)
全局默认单价
    ↓ (不存在)
系统兜底（46元/KG）
```

### 2.3 5种计价方式

1. **固定单价** - 统一单价（如：46元/KG）
2. **阶梯价格** - 按重量区间定价（每个订单单独计算）
3. **线路价格** - 按物流线路定价
4. **区间价格** - 按日期区间定价
5. **自定义公式** - 支持简单数学运算（如：{weight} * 46 + 10）

### 2.4 支付状态同步

- 标记账单为已支付时，同步更新关联订单
- 采用容错机制：记录错误但继续执行
- 返回成功和失败的订单列表

### 2.5 账单作废规则

- 只能作废未支付账单（pay_status=1）
- 不需要填写作废原因
- 作废后订单恢复待出账状态
- 账单标记为已作废，仍显示在列表中

### 2.6 并发控制

- 使用数据库悲观锁（SELECT ... FOR UPDATE）
- 生成账单前锁定订单
- 跳过已归档订单并提示

### 2.7 文件管理

- Excel存储路径：`./uploads/statements/`
- 文件命名：`ST20260228001_31398.xlsx`
- 保留历史版本（时间戳区分）
- 无权限控制，无过期清理

---

## 三、架构设计

### 3.1 整体架构

```
┌─────────────────────────────────────────────┐
│              前端层 (View)                   │
│  - 订单选择页面                              │
│  - 账单列表页面                              │
│  - 财务配置页面                              │
└─────────────────────────────────────────────┘
                    ↓ HTTP
┌─────────────────────────────────────────────┐
│            控制器层 (Controller)             │
│  - Package/Statement.php (薄控制器)         │
│  - Finance/Config.php (薄控制器)            │
└─────────────────────────────────────────────┘
                    ↓ 调用
┌─────────────────────────────────────────────┐
│             服务层 (Service)                 │
│  - StatementService (账单生成)              │
│  - StatementManageService (账单管理)        │
│  - PriceCalculator (计价引擎)               │
│  - FinanceConfigService (财务配置)          │
│  - ExcelService (Excel生成)                 │
└─────────────────────────────────────────────┘
                    ↓ 操作
┌─────────────────────────────────────────────┐
│              模型层 (Model)                  │
│  - Statement (账单模型)                      │
│  - Package (订单模型)                        │
│  - FinanceConfig (配置模型)                  │
└─────────────────────────────────────────────┘
                    ↓ 存储
┌─────────────────────────────────────────────┐
│              数据层 (Database)               │
│  - yoshop_statement (账单表)                │
│  - yoshop_package (订单表)                  │
│  - yoshop_finance_config (配置表)           │
└─────────────────────────────────────────────┘
```

### 3.2 目录结构

```
source/application/store/
├── controller/
│   ├── package/
│   │   └── Statement.php          # 账单控制器
│   └── finance/
│       └── Config.php              # 财务配置控制器
├── service/
│   ├── statement/
│   │   ├── StatementService.php       # 账单生成服务
│   │   ├── StatementManageService.php # 账单管理服务
│   │   ├── PriceCalculator.php        # 计价引擎
│   │   └── FormulaCalculator.php      # 公式计算器
│   ├── finance/
│   │   └── FinanceConfigService.php   # 财务配置服务
│   └── excel/
│       └── ExcelService.php           # Excel生成服务
├── model/
│   ├── Statement.php              # 账单模型
│   └── FinanceConfig.php          # 财务配置模型
└── view/
    ├── package/
    │   └── statement/
    │       ├── index.php          # 订单选择页面
    │       ├── list.php           # 账单列表
    │       └── detail.php         # 账单详情
    └── finance/
        └── config/
            └── index.php          # 财务配置页面
```

---

## 四、数据库设计

### 4.1 账单表 (yoshop_statement)

```sql
CREATE TABLE `yoshop_statement` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '账单ID',
  `statement_no` varchar(20) NOT NULL COMMENT '账单编号',
  `member_id` int(11) unsigned NOT NULL COMMENT '客户ID',
  `member_name` varchar(50) NOT NULL COMMENT '客户姓名',
  `start_date` date DEFAULT NULL COMMENT '账单开始日期',
  `end_date` date DEFAULT NULL COMMENT '账单结束日期',
  `total_packages` int(11) NOT NULL DEFAULT '0' COMMENT '订单数量',
  `total_weight` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总重量(KG)',
  `total_amount` decimal(10,2) NOT NULL DEFAULT '0.00' COMMENT '总金额(元)',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(1正常 2已作废)',
  `pay_status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '支付状态(1未支付 2已支付)',
  `pay_time` datetime DEFAULT NULL COMMENT '支付时间',
  `pay_remark` varchar(255) DEFAULT NULL COMMENT '支付备注',
  `excel_path` varchar(255) DEFAULT NULL COMMENT 'Excel文件路径',
  `wxapp_id` int(11) unsigned NOT NULL COMMENT '小程序ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_statement_no` (`statement_no`),
  KEY `idx_member` (`member_id`, `wxapp_id`),
  KEY `idx_create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='账单表';
```

### 4.2 财务配置表 (yoshop_finance_config)

```sql
CREATE TABLE `yoshop_finance_config` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '配置ID',
  `member_id` int(11) unsigned DEFAULT NULL COMMENT '客户ID(NULL为全局配置)',
  `price_type` tinyint(1) NOT NULL DEFAULT '1' COMMENT '计价方式(1固定 2阶梯 3线路 4区间 5公式)',
  `unit_price` decimal(10,2) DEFAULT NULL COMMENT '固定单价',
  `price_tier_json` text COMMENT '阶梯价格JSON',
  `price_line_json` text COMMENT '线路价格JSON',
  `price_range_json` text COMMENT '区间价格JSON',
  `price_formula` varchar(255) DEFAULT NULL COMMENT '自定义公式',
  `status` tinyint(1) NOT NULL DEFAULT '1' COMMENT '状态(0禁用 1启用)',
  `wxapp_id` int(11) unsigned NOT NULL COMMENT '小程序ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_member` (`member_id`, `wxapp_id`, `status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='财务配置表';
```

### 4.3 历史单价表 (yoshop_history_price)

```sql
CREATE TABLE `yoshop_history_price` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'ID',
  `member_id` int(11) unsigned NOT NULL COMMENT '客户ID',
  `unit_price` decimal(10,2) NOT NULL COMMENT '单价',
  `wxapp_id` int(11) unsigned NOT NULL COMMENT '小程序ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uk_member` (`member_id`, `wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='历史单价表';
```

### 4.4 Excel模板配置表 (yoshop_statement_template)

```sql
CREATE TABLE `yoshop_statement_template` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '模板ID',
  `template_name` varchar(50) NOT NULL COMMENT '模板名称',
  `title` varchar(100) DEFAULT NULL COMMENT '账单标题',
  `logo_path` varchar(255) DEFAULT NULL COMMENT 'LOGO路径',
  `alipay_qr_path` varchar(255) DEFAULT NULL COMMENT '支付宝二维码路径',
  `wechat_qr_path` varchar(255) DEFAULT NULL COMMENT '微信二维码路径',
  `notice_text` text COMMENT '温馨提示文本',
  `is_default` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否默认模板',
  `wxapp_id` int(11) unsigned NOT NULL COMMENT '小程序ID',
  `create_time` datetime NOT NULL COMMENT '创建时间',
  `update_time` datetime NOT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_wxapp` (`wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='账单模板表';
```

### 4.5 订单表修改 (yoshop_package)

```sql
-- 添加账单关联字段
ALTER TABLE `yoshop_package` 
ADD COLUMN `statement_id` int(11) unsigned DEFAULT NULL COMMENT '账单ID' AFTER `is_pay`,
ADD INDEX `idx_statement` (`statement_id`);
```

---

## 五、组件设计

### 5.1 账单编号生成器

**格式**：ST + YYYYMMDD + 3位流水号

**示例**：ST20260228001

**实现**：
```php
class Statement extends Model
{
    public static function generateStatementNo()
    {
        $prefix = 'ST';
        $date = date('Ymd');
        
        Db::startTrans();
        try {
            $lastStatement = self::where('statement_no', 'like', $prefix . $date . '%')
                ->lock(true)
                ->order('statement_no', 'desc')
                ->find();
            
            $nextNo = $lastStatement ? intval(substr($lastStatement['statement_no'], -3)) + 1 : 1;
            $statementNo = $prefix . $date . str_pad($nextNo, 3, '0', STR_PAD_LEFT);
            
            Db::commit();
            return $statementNo;
        } catch (\Exception $e) {
            Db::rollback();
            throw $e;
        }
    }
}
```

### 5.2 计价引擎

**职责**：根据配置计算订单金额

**接口**：
```php
interface PriceCalculatorInterface
{
    public function calculate($packages, $priceConfig);
}
```

**实现**：
```php
class PriceCalculator
{
    public function calculate($packages, $priceConfig)
    {
        foreach ($packages as &$package) {
            $unitPrice = $this->calculateUnitPrice($package, $priceConfig);
            $package['unit_price'] = $unitPrice;
            $package['amount'] = $package['weight'] * $unitPrice;
        }
        return $packages;
    }
    
    private function calculateUnitPrice($package, $priceConfig)
    {
        switch ($priceConfig['price_type']) {
            case 1: return $this->calculateFixedPrice($priceConfig);
            case 2: return $this->calculateTierPrice($package, $priceConfig);
            case 3: return $this->calculateLinePrice($package, $priceConfig);
            case 4: return $this->calculateRangePrice($package, $priceConfig);
            case 5: return $this->calculateFormulaPrice($package, $priceConfig);
            default: return 46.00;
        }
    }
}
```

### 5.3 公式计算器

**技术选型**：Symfony Expression Language

**实现**：
```php
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class FormulaCalculator
{
    private $expressionLanguage;
    
    public function __construct()
    {
        $this->expressionLanguage = new ExpressionLanguage();
    }
    
    public function calculate($formula, $weight)
    {
        $expression = str_replace(['{', '}'], '', $formula);
        $result = $this->expressionLanguage->evaluate($expression, ['weight' => $weight]);
        return round($result, 2);
    }
    
    public function validate($formula)
    {
        try {
            $expression = str_replace(['{', '}'], '', $formula);
            $this->expressionLanguage->parse($expression, ['weight']);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
```

### 5.4 Excel生成服务

**技术选型**：PhpSpreadsheet

**结构**：
```
Row 1-3:   LOGO区域
Row 4:     标题
Row 5-8:   账单信息
Row 9:     空行
Row 10:    表头
Row 11+:   订单明细
Row N:     汇总
Row N+1:   收款二维码
Row N+2:   温馨提示
```

**实现**：
```php
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ExcelService
{
    public function generateStatementExcel($statement, $packages, $template)
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        $currentRow = 1;
        $currentRow = $this->insertLogo($sheet, $template, $currentRow);
        $currentRow = $this->insertTitle($sheet, $template, $currentRow);
        $currentRow = $this->insertStatementInfo($sheet, $statement, $currentRow);
        $currentRow = $this->insertPackageTable($sheet, $packages, $currentRow);
        $currentRow = $this->insertSummary($sheet, $statement, $currentRow);
        $currentRow = $this->insertPaymentQR($sheet, $template, $currentRow);
        $this->insertNotice($sheet, $template, $currentRow);
        
        $fileName = $statement['statement_no'] . '_' . $statement['member_id'] . '.xlsx';
        $filePath = './uploads/statements/' . $fileName;
        
        $writer = new Xlsx($spreadsheet);
        $writer->setPreCalculateFormulas(false);
        $writer->save($filePath);
        
        return $filePath;
    }
}
```

---

## 六、接口设计

### 6.1 账单生成接口

**URL**：`POST /store/statement/create`

**请求参数**：
```json
{
  "package_ids": [1, 2, 3],
  "member_id": 31398
}
```

**响应**：
```json
{
  "code": 1,
  "msg": "账单生成成功",
  "data": {
    "statement_id": 123,
    "statement_no": "ST20260228001",
    "total_amount": 2300.00
  }
}
```

### 6.2 账单列表接口

**URL**：`GET /store/statement/list`

**请求参数**：
```
page: 1
member_id: 31398 (可选)
pay_status: 1 (可选)
start_date: 2026-02-01 (可选)
end_date: 2026-02-28 (可选)
```

**响应**：
```json
{
  "code": 1,
  "msg": "success",
  "data": {
    "list": [
      {
        "id": 123,
        "statement_no": "ST20260228001",
        "member_name": "张三",
        "total_packages": 5,
        "total_amount": 2300.00,
        "pay_status": 1,
        "create_time": "2026-02-28 10:30:00"
      }
    ],
    "total": 10
  }
}
```

### 6.3 标记已支付接口

**URL**：`POST /store/statement/markPaid`

**请求参数**：
```json
{
  "statement_id": 123,
  "remark": "已收款"
}
```

**响应**：
```json
{
  "code": 1,
  "msg": "操作成功",
  "data": {
    "success_count": 5,
    "failed_count": 0,
    "failed_ids": []
  }
}
```

### 6.4 作废账单接口

**URL**：`POST /store/statement/void`

**请求参数**：
```json
{
  "statement_id": 123
}
```

**响应**：
```json
{
  "code": 1,
  "msg": "账单已作废"
}
```

### 6.5 财务配置保存接口

**URL**：`POST /store/finance/saveConfig`

**请求参数**：
```json
{
  "member_id": 31398,
  "price_type": 2,
  "price_tier_json": {
    "tiers": [
      {"min": 0, "max": 10, "price": 50},
      {"min": 10, "max": 50, "price": 46},
      {"min": 50, "max": null, "price": 42}
    ]
  }
}
```

---

## 七、错误处理

### 7.1 错误码定义

```php
class ErrorCode
{
    const SUCCESS = 1;
    const ERROR = 0;
    
    // 账单相关
    const STATEMENT_NO_PACKAGES = 10001;  // 未选择订单
    const STATEMENT_DIFF_MEMBER = 10002;  // 订单属于不同客户
    const STATEMENT_LOCKED = 10003;       // 订单已被归档
    const STATEMENT_NOT_FOUND = 10004;    // 账单不存在
    const STATEMENT_PAID = 10005;         // 账单已支付
    
    // 配置相关
    const CONFIG_INVALID_FORMULA = 20001; // 公式语法错误
    const CONFIG_INVALID_TIER = 20002;    // 阶梯配置错误
}
```

### 7.2 异常处理

```php
try {
    $statement = $this->service->createStatement($packageIds, $memberId);
    return $this->renderSuccess('账单生成成功', $statement);
} catch (\Exception $e) {
    \think\Log::error('账单生成失败: ' . $e->getMessage());
    return $this->renderError($e->getMessage());
}
```

---

## 八、验收标准

### 8.1 功能验收

**Given-When-Then格式**：

1. **账单生成**
   - Given: 财务人员选择了同一客户的5个待出账订单
   - When: 点击"生成账单"按钮
   - Then: 系统生成账单，订单被标记为已归档，生成Excel文件

2. **计价计算**
   - Given: 客户配置了阶梯价格（0-10KG: 50元，10+KG: 46元）
   - When: 生成包含8KG和15KG订单的账单
   - Then: 8KG订单使用50元/KG，15KG订单使用46元/KG

3. **并发控制**
   - Given: 两个财务人员同时为相同订单生成账单
   - When: 第一个人先提交
   - Then: 第二个人提交时提示订单已被归档

4. **支付同步**
   - Given: 账单包含5个订单
   - When: 标记账单为已支付
   - Then: 账单和所有订单的支付状态都更新为已支付

5. **账单作废**
   - Given: 存在一个未支付的账单
   - When: 点击"作废"按钮
   - Then: 账单标记为已作废，订单恢复待出账状态

### 8.2 性能验收

- 生成包含100个订单的账单 < 3秒
- Excel文件生成 < 2秒
- 账单列表加载 < 1秒

### 8.3 安全验收

- 自定义公式不能执行系统命令
- 并发生成不会产生重复账单编号
- 文件上传验证类型和大小

---

## 九、测试策略

### 9.1 单元测试

- 计价引擎各种计价方式
- 公式计算器语法验证
- 账单编号生成唯一性

### 9.2 集成测试

- 账单生成完整流程
- 支付状态同步
- 账单作废流程

### 9.3 性能测试

- 大批量订单处理
- 并发账单生成
- Excel生成性能

---

## 十、附录

### 10.1 技术选型

| 组件 | 技术 | 理由 |
|------|------|------|
| Excel生成 | PhpSpreadsheet | 功能完善，社区活跃 |
| 公式解析 | Symfony Expression Language | 安全可靠，沙箱环境 |
| 架构模式 | 服务层模式 | 职责分离，易于测试 |
| 并发控制 | 悲观锁 | 数据一致性保证 |
| 编号生成 | 日期+序号 | 可读性好，便于查询 |

### 10.2 研究文档索引

1. [PhpSpreadsheet研究](research/01-phpspreadsheet.md)
2. [数据库事务和锁](research/02-database-transactions.md)
3. [服务层架构模式](research/03-service-layer-pattern.md)
4. [表达式解析](research/04-expression-parser.md)
5. [文件存储策略](research/05-file-storage.md)
6. [账单编号生成](research/06-statement-number-generation.md)
7. [前端交互设计](research/07-frontend-interaction.md)

### 10.3 替代方案

**公式解析**：
- 主方案：Symfony Expression Language
- 备选：正则验证 + bc_math

**编号生成**：
- 主方案：数据库锁 + 日期序号
- 备选：Redis INCR

**Excel生成**：
- 主方案：PhpSpreadsheet
- 备选：PHPExcel（已停止维护）

---

**文档结束**
