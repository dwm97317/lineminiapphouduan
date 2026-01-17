# Implementation Plan: 快递面单打印功能

**Branch**: `004-express-waybill-printing` | **Date**: 2026-01-17 | **Spec**: [requirements.md](./requirements.md)

## Summary

在订单列表页面添加中通和顺丰快递面单打印功能，支持单个和批量打印，包含预览、打印、下单等操作，并在订单详情页显示打印历史记录。

## Technical Context

**Language/Version**: PHP 7.2+ (ThinkPHP 5.x)
**Primary Dependencies**: 
- ThinkPHP 5.x (后端框架)
- jQuery 3.x (前端交互)
- AmazeUI (UI 框架)
- Picqer/Barcode (条形码生成)
**Storage**: MySQL 5.7+
**Testing**: 手动测试 + PHP 单元测试
**Target Platform**: Linux/Windows Server + 现代浏览器
**Project Type**: Web 应用
**Performance Goals**: 
- API 响应时间 < 3秒
- 面单预览加载 < 5秒
- 批量打印每个订单处理 < 2秒
**Constraints**: 
- 需要对接中通和顺丰快递 API
- 打印依赖浏览器 window.print() 功能
- 面单格式需符合快递公司规范
**Scale/Scope**: 
- 预计日均打印量 500-1000 单
- 支持并发 10-20 个用户同时打印

## Steering Check

*GATE: Must pass before implementation*

### Product Alignment
- [x] 符合集运系统业务流程
- [x] 提升仓库操作效率
- [x] 满足多快递公司支持需求

### Tech Compliance
- [x] 使用现有 ThinkPHP 框架
- [x] 遵循 MVC 架构模式
- [x] 符合项目代码规范

### Structure Compliance
- [x] 遵循现有项目目录结构
- [x] 使用标准命名约定
- [x] 模块化设计便于扩展

## Project Structure

### Documentation (this feature)
```text
.kiro/specs/004-express-waybill-printing/
├── requirements.md      # 需求规范
├── design.md           # 本文件 - 技术设计
├── data-model.md       # 数据模型
├── contracts/          # API 接口定义
│   ├── zhongtong-api.md
│   └── shunfeng-api.md
├── quickstart.md       # 快速验证场景
└── tasks.md            # 任务列表
```

### Source Code
```text
Lineminiapp/source/application/
├── store/
│   ├── controller/
│   │   ├── TrOrder.php                    # 添加打印相关方法
│   │   └── setting/
│   │       └── WaybillConfig.php          # 新增：面单配置控制器
│   ├── model/
│   │   ├── Inpack.php                     # 订单模型（已存在）
│   │   └── WaybillRecord.php              # 新增：面单打印记录模型
│   └── view/
│       ├── tr_order/
│       │   ├── index.php                   # 修改：添加打印按钮
│       │   ├── waybill_preview.php         # 新增：面单预览页面
│       │   └── orderdetail.php             # 修改：添加打印历史
│       └── setting/
│           └── waybill_config/
│               └── index.php               # 新增：面单配置页面
├── common/
│   ├── library/
│   │   └── express/
│   │       ├── ExpressInterface.php        # 新增：快递接口
│   │       ├── ZhongtongExpress.php        # 新增：中通快递类
│   │       └── ShunfengExpress.php         # 新增：顺丰快递类
│   ├── service/
│   │   ├── WaybillService.php              # 新增：面单服务类
│   │   └── WaybillConfigService.php        # 新增：面单配置服务类
│   └── model/
│       └── Setting.php                     # 已存在：配置模型
└── database/
    └── migrations/
        └── 20260117_create_waybill_record_table.php  # 数据库迁移
```

## Architecture Design

### 1. 系统架构

```
┌─────────────────────────────────────────────────────────────────┐
│                    浏览器 (Browser)                              │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐          │
│  │ 订单列表页面  │  │ 面单预览窗口  │  │ 打印对话框    │          │
│  └──────────────┘  └──────────────┘  └──────────────┘          │
│  ┌──────────────────────────────────────────────────┐          │
│  │         面单配置管理页面                          │          │
│  └──────────────────────────────────────────────────┘          │
└─────────────────────────────────────────────────────────────────┘
                          ↓ AJAX
┌─────────────────────────────────────────────────────────────────┐
│              ThinkPHP 后端 (Backend)                             │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         TrOrder Controller                                │   │
│  │  - printWaybill()      打印面单                           │   │
│  │  - batchPrintWaybill() 批量打印                           │   │
│  │  - createWaybillOrder() 只下单                            │   │
│  │  - getWaybillHistory() 获取打印历史                       │   │
│  └──────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         WaybillConfig Controller                          │   │
│  │  - index()             配置页面                           │   │
│  │  - getConfig()         获取配置                           │   │
│  │  - saveConfig()        保存配置                           │   │
│  │  - getFieldList()      获取字段列表                       │   │
│  │  - resetConfig()       恢复默认配置                       │   │
│  └──────────────────────────────────────────────────────────┘   │
│                          ↓                                       │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         WaybillService                                    │   │
│  │  - generateWaybill()   生成面单（根据配置）               │   │
│  │  - validateOrder()     验证订单                           │   │
│  │  - saveRecord()        保存记录                           │   │
│  │  - applyConfig()       应用配置到面单                     │   │
│  └──────────────────────────────────────────────────────────┘   │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         WaybillConfigService                              │   │
│  │  - getConfig()         获取配置                           │   │
│  │  - saveConfig()        保存配置                           │   │
│  │  - getDefaultConfig()  获取默认配置                       │   │
│  │  - validateConfig()    验证配置                           │   │
│  │  - getFieldDefinitions() 获取字段定义                     │   │
│  └──────────────────────────────────────────────────────────┘   │
│                          ↓                                       │
│  ┌──────────────┐  ┌──────────────┐                             │
│  │ Zhongtong    │  │ Shunfeng     │                             │
│  │ Express      │  │ Express      │                             │
│  └──────────────┘  └──────────────┘                             │
│                          ↓                                       │
│  ┌──────────────────────────────────────────────────────────┐   │
│  │         Setting Model (配置存储)                          │   │
│  │  - waybill_config_zhongtong                               │   │
│  │  - waybill_config_shunfeng                                │   │
│  └──────────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────────┘
                          ↓ HTTP API
┌─────────────────────────────────────────────────────────────────┐
│              快递公司 API (Express API)                          │
│  ┌──────────────┐  ┌──────────────┐                             │
│  │ 中通 API      │  │ 顺丰 API      │                             │
│  └──────────────┘  └──────────────┘                             │
└─────────────────────────────────────────────────────────────────┘
```

### 2. 核心流程

#### 2.1 单个打印流程
```
用户点击"打印中通" 
  → 前端发送 AJAX 请求 (order_id, express_type)
  → 后端获取订单信息 (Inpack)
  → 通过 address_id 查询收货地址 (UserAddress)
  → 验证地址信息完整性
  → 调用中通 API 获取面单数据
  → 生成面单 HTML（包含收货地址信息）
  → 返回前端显示预览
  → 用户选择操作：
     - 立即打印 → window.print() → 记录日志
     - 只下单 → 调用 API 创建订单 → 保存运单号 → 记录日志
     - 取消 → 关闭窗口
```

#### 2.2 批量打印流程
```
用户勾选多个订单 + 点击"批量打印中通"
  → 前端发送 AJAX 请求 (order_ids[], express_type)
  → 后端返回订单列表
  → 前端循环处理：
     - 显示第 N 个订单预览
     - 用户选择：
       * 打印并继续 → 打印 → 下一个
       * 只下单并继续 → 下单 → 下一个
       * 跳过 → 下一个
       * 取消批量 → 停止循环
  → 所有订单处理完成
  → 显示统计信息（成功/跳过/失败）
```

### 3. 数据流

#### 3.1 打印流程数据流
```
订单数据 (Inpack)
  ↓
获取 address_id
  ↓
查询收货地址 (UserAddress)
  ↓
验证地址完整性
  ↓
获取面单配置 (WaybillConfigService)
  ↓
调用快递 API
  ↓
应用配置过滤字段
  ↓
生成面单 HTML (包含条形码和收货地址)
  ↓
显示预览
  ↓
用户操作
  ↓
记录日志 (WaybillRecord)
```

#### 3.2 配置管理数据流
```
管理员访问配置页面
  ↓
加载当前配置 (Setting 表)
  ↓
修改配置项
  ↓
验证配置数据
  ↓
保存到 Setting 表 (JSON 格式)
  ↓
下次打印时自动应用新配置
```

## Technology Decisions

### 1. 快递 API 集成方式

**Decision**: 使用策略模式 + 接口抽象

**Rationale**:
- 不同快递公司 API 差异大，需要统一接口
- 便于后续扩展其他快递公司
- 符合开闭原则，易于维护

**Implementation**:
```php
interface ExpressInterface {
    public function createOrder($orderData);
    public function getWaybill($orderData);
    public function cancelOrder($waybillNo);
}

class ZhongtongExpress implements ExpressInterface {
    // 中通具体实现
}

class ShunfengExpress implements ExpressInterface {
    // 顺丰具体实现
}
```

### 2. 面单预览方式

**Decision**: 使用模态窗口 + 服务端渲染 HTML

**Rationale**:
- 面单格式复杂，服务端渲染更可控
- 模态窗口不影响主页面状态
- 便于打印时隐藏不必要元素

**Alternatives Rejected**:
- 纯前端渲染：面单格式复杂，前端难以维护
- 新窗口打开：用户体验不佳，需要管理多个窗口

### 3. 批量打印实现

**Decision**: 前端循环 + 逐个预览确认

**Rationale**:
- 避免一次性打印错误订单
- 给用户每个订单的确认机会
- 符合用户实际操作习惯

**Alternatives Rejected**:
- 一键打印所有：风险高，无法中途纠错
- 后台队列处理：用户无法实时控制

### 4. 打印日志存储

**Decision**: 独立表存储 + 订单详情页展示

**Rationale**:
- 日志数据量大，独立表便于查询和清理
- 订单详情页展示满足业务需求
- 不需要复杂的日志管理界面

### 5. 面单配置存储

**Decision**: 使用 Setting 表存储 JSON 格式配置

**Rationale**:
- 配置数据结构灵活，JSON 格式便于扩展
- 利用现有 Setting 表，无需新建表
- 每个快递公司独立配置项，互不影响
- 便于版本控制和备份

**Configuration Keys**:
- `waybill_config_zhongtong`: 中通快递配置
- `waybill_config_shunfeng`: 顺丰快递配置

**Alternatives Rejected**:
- 独立配置表：增加复杂度，Setting 表已足够
- 配置文件：不便于动态修改，需要重启系统

## Database Schema

详见 [data-model.md](./data-model.md)

### Setting 表配置项

```sql
-- 中通快递配置
INSERT INTO yoshop_setting (store_id, `key`, `values`, describe, update_time) 
VALUES (10001, 'waybill_config_zhongtong', '{"fields":{"sender_name":true,"sender_phone":true,"sender_address":true,"receiver_name":true,"receiver_phone":true,"receiver_address":true,"item_name":true,"weight":true,"volume":false,"remark":false,"quantity":true},"company_fields":{"site_code":"","site_name":""},"print_params":{"paper_size":"76x130","orientation":"portrait","scale":100}}', '中通快递面单配置', NOW());

-- 顺丰快递配置
INSERT INTO yoshop_setting (store_id, `key`, `values`, `describe`, update_time) 
VALUES (10001, 'waybill_config_shunfeng', '{"fields":{"sender_name":true,"sender_phone":true,"sender_address":true,"receiver_name":true,"receiver_phone":true,"receiver_address":true,"item_name":true,"weight":true,"volume":false,"remark":false,"quantity":true},"company_fields":{"monthly_card":"","payment_method":"1"},"print_params":{"paper_size":"76x130","orientation":"portrait","scale":100}}', '顺丰快递面单配置', NOW());
```

## API Contracts

### 面单配置 API

#### 1. 获取配置
```
GET /store/setting.waybill_config/getConfig
参数: express_type (zhongtong|shunfeng)
返回: {
  "code": 200,
  "msg": "success",
  "data": {
    "fields": {...},
    "company_fields": {...},
    "print_params": {...}
  }
}
```

#### 2. 保存配置
```
POST /store/setting.waybill_config/saveConfig
参数: {
  "express_type": "zhongtong",
  "config": {
    "fields": {...},
    "company_fields": {...},
    "print_params": {...}
  }
}
返回: {
  "code": 200,
  "msg": "保存成功"
}
```

#### 3. 获取字段列表
```
GET /store/setting.waybill_config/getFieldList
参数: express_type (zhongtong|shunfeng)
返回: {
  "code": 200,
  "msg": "success",
  "data": {
    "fields": [
      {"key": "sender_name", "label": "寄件人姓名", "required": true},
      {"key": "weight", "label": "重量", "required": false},
      ...
    ],
    "company_fields": [...],
    "print_params": [...]
  }
}
```

#### 4. 恢复默认配置
```
POST /store/setting.waybill_config/resetConfig
参数: express_type (zhongtong|shunfeng)
返回: {
  "code": 200,
  "msg": "已恢复默认配置"
}
```

详见 [contracts/](./contracts/) 目录

## Security Considerations

1. **权限控制**: 只有具有"打印面单"权限的用户才能访问打印功能
2. **配置管理权限**: 只有系统管理员才能访问面单配置管理页面
3. **数据验证**: 所有用户输入必须验证和过滤
4. **配置验证**: 保存配置前必须验证数据格式和字段有效性
5. **API 密钥保护**: 快递 API 密钥存储在配置文件中，不暴露给前端
6. **日志审计**: 记录所有打印操作和配置修改，包含操作人和时间
7. **防重复提交**: 使用 token 机制防止重复打印
8. **XSS 防护**: 配置数据输出到前端时进行转义处理

## Performance Optimization

1. **配置缓存**: 面单配置加载后缓存到内存，避免每次打印都查询数据库
2. **API 缓存**: 对于相同订单的重复请求，缓存 API 响应 5 分钟
3. **异步加载**: 批量打印时，预加载下一个订单数据
4. **数据库索引**: 在 waybill_record 表的 order_id 和 created_at 字段添加索引
5. **HTML 压缩**: 面单 HTML 进行压缩，减少传输大小
6. **配置预加载**: 页面加载时预加载配置，避免打印时等待

## Error Handling

1. **订单不存在**: 提示"订单不存在"，不允许打印
2. **地址ID为空**: 提示"订单未设置收货地址，请先选择收货地址"，不允许打印
3. **地址不存在**: 提示"收货地址不存在（address_id: XXX），请重新选择地址"，不允许打印
4. **地址信息不完整**: 提示用户补全必填信息（姓名、电话、详细地址），不允许打印
5. **API 调用失败**: 显示具体错误信息，允许重试
6. **网络超时**: 30 秒超时，显示超时提示
7. **打印机未连接**: 浏览器会自动提示，无需额外处理
8. **并发冲突**: 使用乐观锁防止同一订单被多次打印

## Testing Strategy

### 单元测试
- WaybillService 各方法测试
- Express 类的 API 调用测试
- 数据验证逻辑测试

### 集成测试
- 完整打印流程测试
- 批量打印流程测试
- API 错误处理测试

### 手动测试
- 不同浏览器兼容性测试
- 打印机实际打印测试
- 面单格式验证

## Deployment Considerations

1. **数据库迁移**: 执行 waybill_record 表创建脚本
2. **配置初始化**: 在 Setting 表中插入默认面单配置（中通和顺丰）
3. **API 配置**: 添加中通和顺丰 API 配置（密钥、接口地址）
4. **权限配置**: 
   - 添加"打印面单"权限到角色管理
   - 添加"面单配置管理"权限到系统管理员角色
5. **菜单配置**: 在系统设置菜单下添加"面单配置"菜单项
6. **模板部署**: 上传面单模板文件
7. **依赖检查**: 确认 Picqer/Barcode 库已安装

## Rollback Plan

1. **数据库回滚**: 保留迁移脚本的 down() 方法
2. **代码回滚**: 使用 Git 回滚到上一版本
3. **配置回滚**: 备份原配置文件
4. **数据保护**: waybill_record 表数据不删除，只停用功能

## Future Enhancements

1. **更多快递公司**: 扩展支持圆通、韵达等
2. **面单模板编辑器**: 可视化拖拽编辑面单布局和样式
3. **批量导出**: 导出打印记录为 Excel
4. **自动打印**: 订单状态变更时自动触发打印
5. **打印队列**: 支持打印队列管理，避免并发冲突
6. **配置版本管理**: 支持配置历史版本查看和回滚
7. **多店铺配置**: 支持不同店铺使用不同的面单配置
8. **字段映射**: 支持自定义字段映射关系

## Complexity Tracking

无违反 Steering 原则的情况。
