# 集运订单账单归档系统 - 开发文档

## 文档目录

### 📋 项目管理
- [00-项目总览.md](./00-项目总览.md) - 项目背景、模块划分、开发流程

### 🗄️ 数据库
- [01-数据库设计.md](./01-数据库设计.md) - 数据表结构、迁移脚本、索引优化

### ⚙️ 后端开发
- [02-财务配置管理.md](./02-财务配置管理.md) - 配置功能（模板、单价）
- [02-1-历史单价表导入说明.md](./02-1-历史单价表导入说明.md) - 历史单价表导入功能
- [03-账单生成功能.md](./03-账单生成功能.md) - 账单生成逻辑、计价引擎
- [04-账单管理功能.md](./04-账单管理功能.md) - 账单查看、支付、作废
- [05-Excel生成模块.md](./05-Excel生成模块.md) - Excel模板和生成逻辑

### 🎨 前端开发
- [06-前端开发指南.md](./06-前端开发指南.md) - 页面、组件、交互

### 🧪 测试
- [07-测试用例.md](./07-测试用例.md) - 测试场景和用例

### 📥 数据导入
- [08-历史订单支付状态导入.md](./08-历史订单支付状态导入.md) - 历史订单支付状态批量导入
- [08-1-财务原始数据导入详细说明.md](./08-1-财务原始数据导入详细说明.md) - 财务原始数据导入详细说明

## 快速开始

### 1. 数据库准备
```bash
# 执行数据库迁移脚本
mysql -u root -p xinsuju < database/migrations/20260128_create_statement_tables.sql
```

### 2. 安装依赖
```bash
# 安装PhpSpreadsheet（Excel生成）
composer require phpoffice/phpspreadsheet

# 安装表达式解析库（自定义公式）
composer require symfony/expression-language
```

### 3. 配置初始化
```sql
-- 插入全局默认单价
INSERT INTO yoshop_finance_config (
  config_type, config_name, member_id, price_type, unit_price, 
  is_default, status, wxapp_id
) VALUES (
  3, '全局默认单价', NULL, 1, 46.00, 
  1, 1, 10001
);

-- 插入默认账单模板
INSERT INTO yoshop_finance_config (
  config_type, config_name, logo_path, title, 
  alipay_qr_path, wechat_qr_path, notice_text,
  is_default, status, wxapp_id
) VALUES (
  1, '默认模板', './assets/logo.png', '泰国-中国',
  './assets/alipay_qr.png', './assets/wechat_qr.png',
  '请尽快核对账单，谢谢！对好了，请支付到这两个二维码，付款好后请给我截图 谢谢！',
  1, 1, 10001
);
```

### 4. 开发顺序

**第1天**：数据库设计
- 创建数据表
- 编写迁移脚本
- 准备测试数据

**第2-3天**：财务配置管理
- 后端：模型、服务、控制器
- 前端：配置页面、表单、交互

**第4-5天**：账单生成功能
- 后端：账单生成逻辑、计价引擎
- 单元测试

**第6-7天**：账单管理功能
- 后端：查看、支付、作废接口
- 前端：集运订单页面扩展、弹窗

**第8-9天**：Excel生成模块
- Excel模板设计
- 数据填充、图片插入
- 性能优化

**第10-12天**：联调测试
- 功能测试
- 集成测试
- Bug修复

**第13天**：上线部署
- 数据库迁移
- 代码部署
- 验收测试

## 团队分工

| 角色 | 负责人 | 模块 | 工期 |
|------|--------|------|------|
| 数据库工程师 | - | 数据库设计 | 1天 |
| 后端开发A | - | 财务配置管理 | 2天 |
| 后端开发B | - | 账单生成功能 | 2天 |
| 后端开发C | - | 账单管理功能 | 1-2天 |
| 后端开发D | - | Excel生成模块 | 2天 |
| 前端开发A | - | 财务配置页面 | 2-3天 |
| 前端开发B | - | 集运订单页面扩展 | 2天 |
| 测试工程师 | - | 测试用例编写和执行 | 2-3天 |

## 技术栈

### 后端
- PHP 7.2+
- ThinkPHP 5.0
- MySQL 5.7+
- PhpSpreadsheet（Excel生成）
- Symfony Expression Language（公式计算）

### 前端
- jQuery
- Bootstrap
- AdminLTE

## 关键技术点

### 1. 事务处理
```php
Db::startTrans();
try {
    // 业务逻辑
    Db::commit();
} catch (Exception $e) {
    Db::rollback();
    throw $e;
}
```

### 2. 并发控制
```php
// 使用FOR UPDATE锁定订单
$packages = Package::where('id', 'in', $packageIds)
    ->where('statement_id', null)
    ->lock(true)
    ->select();
```

### 3. 配置优先级
```
客户专属配置 > 全局默认配置 > 系统兜底（46元/KG）
```

### 4. Excel生成优化
- 使用流式写入处理大批量数据
- 缓存模板文件
- 异步生成（队列处理）

## 常见问题

### Q1: 如何添加新的计价方式？
1. 在`FinanceConfig`模型添加新的常量
2. 在`StatementService`的`calculateUnitPrice`方法添加新的case
3. 在前端添加对应的配置表单

### Q2: 如何修改Excel模板？
参考`05-Excel生成模块.md`，修改`ExcelService`中的模板逻辑

### Q3: 如何处理历史数据？
旧订单的`is_pay`字段不准确，新系统以`statement_id`和账单的`pay_status`为准

### Q4: 如何优化大批量订单的账单生成？
1. 使用队列异步处理
2. Excel使用流式写入
3. 分批处理订单数据

## 联系方式

如有问题，请联系：
- 项目经理：xxx
- 技术负责人：xxx
- 测试负责人：xxx
