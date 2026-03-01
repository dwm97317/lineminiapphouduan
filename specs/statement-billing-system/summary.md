# 账单系统项目总结

**项目名称**：集运订单账单系统  
**版本**：1.0  
**状态**：设计完成，待实施  
**创建日期**：2026-02-28

---

## 项目概述

为现有集运系统新增账单功能，支持财务人员为客户生成对账单，提供5种灵活的计价方式和专业的Excel导出。采用服务层架构模式，不修改现有代码，提高系统可维护性。

---

## 核心功能

1. **财务配置管理** - 5种计价方式（固定、阶梯、线路、区间、公式）
2. **账单生成** - 选择订单自动计算金额并生成账单
3. **账单管理** - 查看、支付、作废账单
4. **Excel导出** - 生成专业的账单Excel文件
5. **历史单价导入** - 批量导入TXT/Excel格式的历史价格

---

## 技术架构

- **架构模式**：服务层模式（薄控制器 + 厚服务层）
- **Excel生成**：PhpSpreadsheet
- **公式解析**：Symfony Expression Language
- **并发控制**：数据库悲观锁（SELECT ... FOR UPDATE）
- **编号规则**：ST + YYYYMMDD + 3位流水号

---

## 项目文档

### 1. 需求文档
- **文件**：`requirements.md`
- **内容**：10个关键问题的需求澄清记录

### 2. 研究文档
- `research/01-phpspreadsheet.md` - Excel生成库研究
- `research/02-database-transactions.md` - 事务和锁机制
- `research/03-service-layer-pattern.md` - 服务层架构
- `research/04-expression-parser.md` - 表达式解析
- `research/05-file-storage.md` - 文件存储策略
- `research/06-statement-number-generation.md` - 编号生成规则
- `research/07-frontend-interaction.md` - 前端交互设计

### 3. 设计文档
- **文件**：`design.md`
- **内容**：
  - 架构设计（分层架构图）
  - 数据库设计（4个新表）
  - 组件设计（编号生成、计价引擎、Excel生成）
  - 接口设计（RESTful API）
  - 验收标准（Given-When-Then格式）

### 4. 实施计划
- **文件**：`plan.md`
- **内容**：10步增量实施计划，每步都产生可工作的功能

---

## 数据库设计

### 新增表
1. `yoshop_statement` - 账单表
2. `yoshop_finance_config` - 财务配置表
3. `yoshop_history_price` - 历史单价表
4. `yoshop_statement_template` - Excel模板表

### 修改表
- `yoshop_package` - 添加`statement_id`字段

---

## 实施步骤

1. ✅ 数据库设计与初始化
2. ✅ 核心模型层开发
3. ✅ 计价引擎实现
4. ✅ 账单生成服务（核心流程）
5. ✅ Excel生成服务
6. ✅ 账单管理服务
7. ✅ 财务配置服务
8. ✅ 控制器层开发
9. ✅ 前端页面开发
10. ✅ 集成测试与优化

---

## 关键技术决策

| 决策点 | 选择 | 理由 |
|--------|------|------|
| 架构模式 | 服务层模式 | 职责分离，易于测试和维护 |
| Excel生成 | PhpSpreadsheet | 功能完善，社区活跃 |
| 公式解析 | Symfony Expression Language | 安全可靠，沙箱环境 |
| 并发控制 | 悲观锁 | 数据一致性保证 |
| 编号生成 | 日期+序号 | 可读性好，便于查询 |
| 阶梯计价 | 每订单单独计算 | 更灵活，符合业务需求 |

---

## 验收标准

### 功能验收
- ✅ 财务人员可以选择订单生成账单
- ✅ 支持5种计价方式自动计算金额
- ✅ 生成专业的Excel文件
- ✅ 标记支付时同步更新订单状态
- ✅ 作废账单后订单恢复待出账状态
- ✅ 并发生成不会产生重复编号

### 性能验收
- 生成100个订单的账单 < 3秒
- Excel文件生成 < 2秒
- 账单列表加载 < 1秒

### 安全验收
- 自定义公式不能执行系统命令
- 文件上传验证类型和大小
- 并发控制保证数据一致性

---

## 下一步行动

### 立即开始
使用以下命令开始实施：

```bash
# 方式1：使用Ralph自动实施（推荐）
ralph run --config presets/pdd-to-code-assist.yml

# 方式2：使用Ralph简化流程
ralph run --config presets/spec-driven.yml

# 方式3：手动实施
# 按照plan.md的步骤逐步实施
```

### 手动实施建议
1. 先阅读`design.md`了解整体架构
2. 按照`plan.md`的10个步骤顺序实施
3. 每完成一步都进行测试
4. 遇到问题参考对应的research文档

---

## 项目文件清单

```
specs/statement-billing-system/
├── rough-idea.md                          # 原始需求
├── requirements.md                        # 需求澄清记录
├── design.md                              # 详细设计文档
├── plan.md                                # 实施计划
├── summary.md                             # 本文档
└── research/                              # 研究文档目录
    ├── 01-phpspreadsheet.md
    ├── 02-database-transactions.md
    ├── 03-service-layer-pattern.md
    ├── 04-expression-parser.md
    ├── 05-file-storage.md
    ├── 06-statement-number-generation.md
    └── 07-frontend-interaction.md
```

---

## 联系与支持

如有问题，请参考：
- 设计文档：`design.md`
- 实施计划：`plan.md`
- 研究文档：`research/` 目录

---

**文档完成时间**：2026-02-28  
**预计开发工期**：10-15个工作日  
**建议团队规模**：2-3人（1后端 + 1前端 + 1测试）
