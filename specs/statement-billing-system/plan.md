# 账单系统实施计划

**项目名称**：集运订单账单系统  
**版本**：1.0  
**创建日期**：2026-02-28

---

## 实施原则

1. **增量开发** - 每步都产生可工作的功能
2. **TDD驱动** - 先写测试，再写实现
3. **端到端优先** - 尽早实现核心流程
4. **无孤立代码** - 每步都集成到系统中

---

## 进度跟踪

- [ ] Step 1: 数据库设计与初始化
- [ ] Step 2: 核心模型层开发
- [ ] Step 3: 计价引擎实现
- [ ] Step 4: 账单生成服务（核心流程）
- [ ] Step 5: Excel生成服务
- [ ] Step 6: 账单管理服务
- [ ] Step 7: 财务配置服务
- [ ] Step 8: 控制器层开发
- [ ] Step 9: 前端页面开发
- [ ] Step 10: 集成测试与优化

---

## Step 1: 数据库设计与初始化

### 目标
创建所有必需的数据库表和索引，为后续开发提供数据基础。

### 实现指导

1. **创建迁移文件**
   - 文件路径：`database/migrations/20260228_create_statement_tables.sql`
   - 包含4个新表：statement、finance_config、history_price、statement_template
   - 修改package表：添加statement_id字段

2. **表结构要点**
   - statement表：唯一索引statement_no，复合索引(member_id, wxapp_id)
   - finance_config表：复合索引(member_id, wxapp_id, status)
   - package表：添加索引idx_statement(statement_id)

3. **初始化数据**
   - 插入全局默认配置（固定单价46元/KG）
   - 插入默认Excel模板

### 测试要求

```sql
-- 验证表创建
SHOW TABLES LIKE 'yoshop_statement%';
SHOW TABLES LIKE 'yoshop_finance_config';
SHOW TABLES LIKE 'yoshop_history_price';

-- 验证索引
SHOW INDEX FROM yoshop_statement;
SHOW INDEX FROM yoshop_package WHERE Key_name = 'idx_statement';

-- 验证初始数据
SELECT * FROM yoshop_finance_config WHERE member_id IS NULL;
SELECT * FROM yoshop_statement_template WHERE is_default = 1;
```

### 集成说明
数据库表创建后，可以开始模型层开发。

### Demo
执行迁移脚本后，数据库包含完整的表结构和初始配置数据。

---

## Step 2: 核心模型层开发

### 目标
实现Statement、FinanceConfig、HistoryPrice三个核心模型，提供基础的数据访问能力。

### 实现指导

1. **Statement模型** (`source/application/store/model/Statement.php`)
   ```php
   class Statement extends Model
   {
       // 状态常量
       const STATUS_NORMAL = 1;
       const STATUS_VOID = 2;
       
       // 支付状态常量
       const PAY_STATUS_UNPAID = 1;
       const PAY_STATUS_PAID = 2;
       
       // 生成账单编号
       public static function generateStatementNo();
       
       // 获取账单详情（含订单列表）
       public function getDetailWithPackages();
       
       // 关联订单
       public function packages();
   }
   ```

2. **FinanceConfig模型** (`source/application/store/model/FinanceConfig.php`)
   ```php
   class FinanceConfig extends Model
   {
       // 计价方式常量
       const PRICE_TYPE_FIXED = 1;
       const PRICE_TYPE_TIER = 2;
       const PRICE_TYPE_LINE = 3;
       const PRICE_TYPE_RANGE = 4;
       const PRICE_TYPE_FORMULA = 5;
       
       // 获取有效配置（优先级：客户 > 历史 > 全局）
       public static function getEffectivePrice($memberId);
   }
   ```

3. **HistoryPrice模型** (`source/application/store/model/HistoryPrice.php`)
   ```php
   class HistoryPrice extends Model
   {
       // 批量导入
       public static function batchImport($data);
       
       // 获取客户历史单价
       public static function getMemberPrice($memberId);
   }
   ```

### 测试要求

```php
// 测试账单编号生成
$no1 = Statement::generateStatementNo();
$no2 = Statement::generateStatementNo();
assert($no1 !== $no2);
assert(preg_match('/^ST\d{8}\d{3}$/', $no1));

// 测试配置获取
$config = FinanceConfig::getEffectivePrice(31398);
assert($config !== null);
assert(isset($config['price_type']));

// 测试历史单价
HistoryPrice::batchImport([
    ['member_id' => 31398, 'unit_price' => 48.00]
]);
$price = HistoryPrice::getMemberPrice(31398);
assert($price == 48.00);
```

### 集成说明
模型层完成后，服务层可以调用这些模型进行业务逻辑处理。

### Demo
可以通过命令行或测试脚本验证模型的基本CRUD操作。

---

## Step 3: 计价引擎实现

### 目标
实现PriceCalculator和FormulaCalculator，支持5种计价方式的金额计算。

### 实现指导

1. **FormulaCalculator** (`source/application/store/service/statement/FormulaCalculator.php`)
   ```php
   class FormulaCalculator
   {
       private $expressionLanguage;
       
       // 计算公式
       public function calculate($formula, $weight);
       
       // 验证公式语法
       public function validate($formula);
   }
   ```

2. **PriceCalculator** (`source/application/store/service/statement/PriceCalculator.php`)
   ```php
   class PriceCalculator
   {
       private $formulaCalculator;
       
       // 批量计算订单金额
       public function calculate($packages, $priceConfig);
       
       // 计算单个订单单价
       private function calculateUnitPrice($package, $priceConfig);
       
       // 5种计价方式
       private function calculateFixedPrice($priceConfig);
       private function calculateTierPrice($package, $priceConfig);
       private function calculateLinePrice($package, $priceConfig);
       private function calculateRangePrice($package, $priceConfig);
       private function calculateFormulaPrice($package, $priceConfig);
   }
   ```

3. **安装依赖**
   ```bash
   composer require symfony/expression-language
   ```

### 测试要求

```php
// 测试固定单价
$calculator = new PriceCalculator();
$packages = [
    ['id' => 1, 'weight' => 10],
    ['id' => 2, 'weight' => 15]
];
$config = ['price_type' => 1, 'unit_price' => 46];
$result = $calculator->calculate($packages, $config);
assert($result[0]['amount'] == 460);
assert($result[1]['amount'] == 690);

// 测试阶梯价格
$config = [
    'price_type' => 2,
    'price_tier_json' => json_encode([
        'tiers' => [
            ['min' => 0, 'max' => 10, 'price' => 50],
            ['min' => 10, 'max' => null, 'price' => 46]
        ]
    ])
];
$result = $calculator->calculate($packages, $config);
assert($result[0]['unit_price'] == 50);
assert($result[1]['unit_price'] == 46);

// 测试自定义公式
$formulaCalc = new FormulaCalculator();
assert($formulaCalc->validate('{weight} * 46 + 10'));
assert($formulaCalc->calculate('{weight} * 46 + 10', 10) == 470);
```

### 集成说明
计价引擎完成后，账单生成服务可以调用它来计算订单金额。

### Demo
创建测试脚本，输入不同的订单和配置，验证计算结果的正确性。

---

## Step 4: 账单生成服务（核心流程）

### 目标
实现StatementService，完成从订单选择到账单生成的核心业务流程。这是系统的核心功能。

### 实现指导

1. **StatementService** (`source/application/store/service/statement/StatementService.php`)
   ```php
   class StatementService
   {
       private $priceCalculator;
       
       // 创建账单（核心方法）
       public function createStatement($packageIds, $memberId);
       
       // 验证和锁定订单
       private function validateAndLockPackages($packageIds, $memberId);
       
       // 创建账单记录
       private function createStatementRecord($packages, $memberId);
       
       // 绑定订单
       private function bindPackages($packageIds, $statementId);
   }
   ```

2. **实现要点**
   - 使用数据库事务保证一致性
   - 使用悲观锁防止并发问题
   - 验证订单属于同一客户
   - 验证订单未被归档
   - 计算金额并创建账单
   - 绑定订单到账单

3. **错误处理**
   - 订单不存在或已删除
   - 订单属于不同客户
   - 订单已被归档
   - 数据库操作失败

### 测试要求

```php
// 测试正常流程
$service = new StatementService();
$statement = $service->createStatement([1, 2, 3], 31398);
assert($statement['statement_no'] !== null);
assert($statement['total_packages'] == 3);
assert($statement['total_amount'] > 0);

// 验证订单已绑定
$package = Package::find(1);
assert($package['statement_id'] == $statement['id']);

// 测试并发控制
try {
    $service->createStatement([1, 2, 3], 31398); // 重复生成
    assert(false, '应该抛出异常');
} catch (\Exception $e) {
    assert(strpos($e->getMessage(), '已归档') !== false);
}

// 测试不同客户
try {
    $service->createStatement([1, 100], 31398); // 订单100属于其他客户
    assert(false, '应该抛出异常');
} catch (\Exception $e) {
    assert(strpos($e->getMessage(), '同一个客户') !== false);
}
```

### 集成说明
账单生成服务完成后，可以通过控制器暴露API接口，前端可以调用生成账单。

### Demo
创建简单的测试页面，选择订单后调用服务生成账单，验证账单记录和订单绑定。

---

## Step 5: Excel生成服务

### 目标
实现ExcelService，生成专业的账单Excel文件。

### 实现指导

1. **ExcelService** (`source/application/store/service/excel/ExcelService.php`)
   ```php
   class ExcelService
   {
       // 生成账单Excel
       public function generateStatementExcel($statement, $packages, $template);
       
       // 插入LOGO
       private function insertLogo($sheet, $template, $currentRow);
       
       // 插入标题
       private function insertTitle($sheet, $template, $currentRow);
       
       // 插入账单信息
       private function insertStatementInfo($sheet, $statement, $currentRow);
       
       // 插入订单明细表格
       private function insertPackageTable($sheet, $packages, $currentRow);
       
       // 插入汇总信息
       private function insertSummary($sheet, $statement, $currentRow);
       
       // 插入收款二维码
       private function insertPaymentQR($sheet, $template, $currentRow);
       
       // 插入温馨提示
       private function insertNotice($sheet, $template, $currentRow);
   }
   ```

2. **安装依赖**
   ```bash
   composer require phpoffice/phpspreadsheet
   ```

3. **样式设计**
   - 标题：大字体、居中、加粗
   - 表头：背景色、边框
   - 数据行：边框、右对齐（金额）
   - 汇总行：加粗、背景色

4. **性能优化**
   - 禁用预计算公式
   - 使用fromArray批量写入
   - 及时释放内存

### 测试要求

```php
// 测试Excel生成
$service = new ExcelService();
$statement = Statement::find(1);
$packages = $statement->packages;
$template = StatementTemplate::getDefault();

$filePath = $service->generateStatementExcel($statement, $packages, $template);
assert(file_exists($filePath));
assert(filesize($filePath) > 0);

// 验证文件可读
$reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReader('Xlsx');
$spreadsheet = $reader->load($filePath);
$sheet = $spreadsheet->getActiveSheet();
assert($sheet->getCell('A1')->getValue() !== null);
```

### 集成说明
Excel生成服务完成后，在账单生成流程中调用，生成Excel文件并保存路径。

### Demo
生成Excel文件后，下载并打开验证格式、内容、样式是否正确。

---

## Step 6: 账单管理服务

### 目标
实现StatementManageService，支持账单查询、支付、作废等管理功能。

### 实现指导

1. **StatementManageService** (`source/application/store/service/statement/StatementManageService.php`)
   ```php
   class StatementManageService
   {
       // 获取账单列表
       public function getList($params);
       
       // 获取账单详情
       public function getDetail($statementId);
       
       // 标记为已支付
       public function markAsPaid($statementId, $remark);
       
       // 作废账单
       public function voidStatement($statementId);
       
       // 重新生成Excel
       public function regenerateExcel($statementId);
   }
   ```

2. **标记已支付实现**
   - 更新账单支付状态
   - 同步更新关联订单
   - 容错处理（记录失败但继续）
   - 返回成功和失败统计

3. **作废账单实现**
   - 验证账单未支付
   - 更新账单状态为已作废
   - 解除订单绑定（statement_id设为NULL）
   - 使用事务保证一致性

### 测试要求

```php
// 测试标记已支付
$service = new StatementManageService();
$result = $service->markAsPaid(1, '已收款');
assert($result['success_count'] > 0);

$statement = Statement::find(1);
assert($statement['pay_status'] == 2);

$packages = $statement->packages;
foreach ($packages as $pkg) {
    assert($pkg['is_pay'] == 1);
}

// 测试作废账单
$statement = Statement::create([...]);
$service->voidStatement($statement['id']);

$statement = Statement::find($statement['id']);
assert($statement['status'] == 2); // 已作废

$packages = Package::where('statement_id', $statement['id'])->select();
assert(count($packages) == 0); // 订单已解绑
```

### 集成说明
账单管理服务完成后，控制器可以调用这些方法提供管理功能。

### Demo
创建测试页面，测试账单列表、详情、支付、作废等功能。

---

## Step 7: 财务配置服务

### 目标
实现FinanceConfigService，支持计价配置的增删改查和历史单价导入。

### 实现指导

1. **FinanceConfigService** (`source/application/store/service/finance/FinanceConfigService.php`)
   ```php
   class FinanceConfigService
   {
       // 保存配置
       public function saveConfig($data);
       
       // 获取配置
       public function getConfig($memberId);
       
       // 删除配置
       public function deleteConfig($configId);
       
       // 导入历史单价（TXT）
       public function importHistoryPriceFromTxt($filePath);
       
       // 导入历史单价（Excel）
       public function importHistoryPriceFromExcel($filePath);
       
       // 上传二维码
       public function uploadQrCode($file, $memberId, $type);
       
       // 上传LOGO
       public function uploadLogo($file, $memberId);
   }
   ```

2. **配置验证**
   - 阶梯价格：验证区间不重叠
   - 自定义公式：验证语法正确
   - 文件上传：验证类型和大小

3. **历史单价导入**
   - TXT：按行解析，支持空格/Tab分隔，忽略#注释
   - Excel：读取第一个Sheet，A列客户ID，B列单价
   - 批量插入，返回成功和失败统计

### 测试要求

```php
// 测试保存配置
$service = new FinanceConfigService();
$service->saveConfig([
    'member_id' => 31398,
    'price_type' => 1,
    'unit_price' => 48.00
]);

$config = FinanceConfig::where('member_id', 31398)->find();
assert($config['unit_price'] == 48.00);

// 测试公式验证
try {
    $service->saveConfig([
        'price_type' => 5,
        'price_formula' => '{weight} * * 46' // 语法错误
    ]);
    assert(false);
} catch (\Exception $e) {
    assert(strpos($e->getMessage(), '语法错误') !== false);
}

// 测试历史单价导入
$txtContent = "31398 48.00\n31966 50.00\n# 注释行";
file_put_contents('/tmp/test.txt', $txtContent);
$result = $service->importHistoryPriceFromTxt('/tmp/test.txt');
assert($result['success_count'] == 2);
```

### 集成说明
财务配置服务完成后，控制器可以调用提供配置管理功能。

### Demo
创建配置页面，测试各种计价方式的保存、历史单价导入、文件上传等功能。

---

## Step 8: 控制器层开发

### 目标
实现薄控制器，暴露RESTful API接口。

### 实现指导

1. **Statement控制器** (`source/application/store/controller/package/Statement.php`)
   ```php
   class Statement extends Controller
   {
       private $service;
       private $manageService;
       
       // 生成账单
       public function create();
       
       // 账单列表
       public function list();
       
       // 账单详情
       public function detail();
       
       // 标记已支付
       public function markPaid();
       
       // 作废账单
       public function void();
       
       // 下载Excel
       public function downloadExcel();
   }
   ```

2. **Config控制器** (`source/application/store/controller/finance/Config.php`)
   ```php
   class Config extends Controller
   {
       private $service;
       
       // 配置页面
       public function index();
       
       // 保存配置
       public function save();
       
       // 获取配置
       public function get();
       
       // 导入历史单价
       public function importHistoryPrice();
       
       // 上传文件
       public function upload();
       
       // 验证公式
       public function validateFormula();
   }
   ```

3. **控制器职责**
   - 接收HTTP请求
   - 验证基本参数
   - 调用服务层
   - 返回JSON响应
   - 不包含业务逻辑

### 测试要求

```php
// 使用HTTP客户端测试API
$client = new GuzzleHttp\Client();

// 测试生成账单
$response = $client->post('/store/statement/create', [
    'json' => [
        'package_ids' => [1, 2, 3],
        'member_id' => 31398
    ]
]);
assert($response->getStatusCode() == 200);
$data = json_decode($response->getBody(), true);
assert($data['code'] == 1);
assert(isset($data['data']['statement_id']));

// 测试账单列表
$response = $client->get('/store/statement/list?page=1');
assert($response->getStatusCode() == 200);
$data = json_decode($response->getBody(), true);
assert(isset($data['data']['list']));
```

### 集成说明
控制器完成后，前端可以通过API调用所有功能。

### Demo
使用Postman或curl测试所有API接口，验证请求和响应格式。

---

## Step 9: 前端页面开发

### 目标
实现用户界面，提供友好的交互体验。

### 实现指导

1. **订单选择页面** (`source/application/store/view/package/statement/index.php`)
   - 客户选择器（支持搜索）
   - 订单列表（支持筛选、勾选）
   - 实时计算（显示已选订单数和总重量）
   - 生成账单按钮

2. **账单列表页面** (`source/application/store/view/package/statement/list.php`)
   - 筛选条件（客户、日期、支付状态）
   - 账单列表（分页）
   - 操作菜单（查看、下载、支付、作废）

3. **账单详情页面** (`source/application/store/view/package/statement/detail.php`)
   - 账单信息卡片
   - 订单明细表格
   - 操作按钮

4. **财务配置页面** (`source/application/store/view/finance/config/index.php`)
   - Tab切换（计价配置、模板配置、历史单价）
   - 计价方式选择
   - 动态表单（根据计价方式显示不同配置）
   - 文件上传

5. **交互优化**
   - 实时验证
   - 加载状态
   - 友好错误提示
   - 响应式设计

### 测试要求

- 在Chrome、Firefox、Safari测试兼容性
- 测试移动端响应式布局
- 测试各种交互场景（正常、异常、边界）
- 验证表单验证和错误提示

### 集成说明
前端页面完成后，整个系统可以端到端使用。

### Demo
演示完整的用户操作流程：
1. 配置计价方式
2. 选择订单生成账单
3. 查看账单详情
4. 下载Excel
5. 标记已支付
6. 作废账单

---

## Step 10: 集成测试与优化

### 目标
进行全面的集成测试，优化性能和用户体验。

### 实现指导

1. **集成测试场景**
   - 完整的账单生成流程
   - 并发生成账单
   - 大批量订单处理（100+）
   - 各种计价方式计算
   - 支付状态同步
   - 账单作废流程
   - 历史单价导入
   - Excel生成和下载

2. **性能优化**
   - 数据库查询优化（添加索引）
   - 分页加载优化
   - Excel生成优化（分批处理）
   - 前端资源压缩

3. **安全加固**
   - SQL注入防护
   - XSS防护
   - CSRF防护
   - 文件上传安全验证

4. **日志完善**
   - 关键操作日志
   - 错误日志
   - 性能日志

### 测试要求

```php
// 性能测试
$startTime = microtime(true);
$service->createStatement($packageIds, $memberId);
$endTime = microtime(true);
assert(($endTime - $startTime) < 3); // 3秒内完成

// 并发测试
$processes = [];
for ($i = 0; $i < 10; $i++) {
    $processes[] = new Process(['php', 'test_concurrent.php']);
}
foreach ($processes as $process) {
    $process->start();
}
// 验证没有重复账单编号

// 大批量测试
$packageIds = range(1, 100);
$statement = $service->createStatement($packageIds, $memberId);
assert($statement['total_packages'] == 100);
```

### 集成说明
集成测试完成后，系统可以上线使用。

### Demo
进行完整的用户验收测试，演示所有功能和场景。

---

## 部署清单

### 1. 环境要求
- PHP >= 7.2
- MySQL >= 5.7
- Composer
- GD扩展（图片处理）

### 2. 依赖安装
```bash
composer require phpoffice/phpspreadsheet
composer require symfony/expression-language
```

### 3. 数据库迁移
```bash
mysql -u root -p database_name < database/migrations/20260228_create_statement_tables.sql
```

### 4. 目录权限
```bash
chmod 755 ./uploads/statements/
chmod 755 ./uploads/qrcode/
chmod 755 ./uploads/logo/
```

### 5. 配置检查
- 数据库连接配置
- 文件上传大小限制
- 时区设置

---

## 风险与应对

| 风险 | 影响 | 应对措施 |
|------|------|----------|
| 并发生成重复编号 | 高 | 使用数据库锁 + 唯一索引 |
| Excel生成超时 | 中 | 分批处理 + 异步生成 |
| 大批量订单性能 | 中 | 添加索引 + 分页加载 |
| 公式安全漏洞 | 高 | 使用沙箱环境 + 语法验证 |
| 文件上传漏洞 | 高 | 严格验证类型和大小 |

---

## 验收标准

参考design.md第八章的验收标准，所有Given-When-Then场景都必须通过。

---

**计划结束**
