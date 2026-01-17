# Data Model: 快递面单打印功能

## Entity: WaybillRecord (面单打印记录)

**Purpose**: 记录每次面单打印或下单操作的详细信息，用于审计和历史查询

**Table Name**: `yoshop_waybill_record`

**Fields**:
- `id`: INT(11) UNSIGNED AUTO_INCREMENT - 主键
- `order_id`: INT(11) UNSIGNED NOT NULL - 关联订单ID (关联 yoshop_inpack.id)
- `order_sn`: VARCHAR(50) NOT NULL - 订单号（冗余字段，便于查询）
- `express_type`: TINYINT(2) NOT NULL - 快递公司类型 (1=中通, 2=顺丰)
- `express_name`: VARCHAR(50) NOT NULL - 快递公司名称
- `waybill_no`: VARCHAR(100) DEFAULT NULL - 运单号
- `operation_type`: TINYINT(2) NOT NULL - 操作类型 (1=打印, 2=只下单)
- `status`: TINYINT(2) DEFAULT 1 - 状态 (1=成功, 2=失败, 3=已取消)
- `api_request`: TEXT DEFAULT NULL - API 请求数据 (JSON)
- `api_response`: TEXT DEFAULT NULL - API 响应数据 (JSON)
- `error_message`: VARCHAR(500) DEFAULT NULL - 错误信息
- `operator_id`: INT(11) UNSIGNED NOT NULL - 操作人ID
- `operator_name`: VARCHAR(50) NOT NULL - 操作人姓名
- `wxapp_id`: INT(11) UNSIGNED NOT NULL DEFAULT 10001 - 小程序ID（多租户）
- `created_at`: DATETIME NOT NULL - 创建时间
- `updated_at`: DATETIME DEFAULT NULL - 更新时间

**Indexes**:
- PRIMARY KEY (`id`)
- INDEX `idx_order_id` (`order_id`) - 按订单查询
- INDEX `idx_waybill_no` (`waybill_no`) - 按运单号查询
- INDEX `idx_created_at` (`created_at`) - 按时间查询
- INDEX `idx_wxapp_id` (`wxapp_id`) - 多租户查询

**Relationships**:
- 多对一关系：WaybillRecord → Inpack (一个订单可以有多条打印记录)
- 多对一关系：WaybillRecord → User (一个用户可以操作多条记录)

**Validation Rules**:
- `order_id` 必须存在于 yoshop_inpack 表
- `express_type` 只能是 1 或 2
- `operation_type` 只能是 1 或 2
- `status` 只能是 1, 2, 或 3
- `waybill_no` 在 operation_type=2 或 status=1 时不能为空

**State Transitions**:
```
初始状态 (创建记录)
  ↓
操作中 (调用 API)
  ↓
成功 (status=1, waybill_no 已填充)
  或
失败 (status=2, error_message 已填充)
  或
取消 (status=3)
```

**Business Rules**:
1. 同一订单可以多次打印，每次都创建新记录
2. 只下单操作必须保存运单号
3. 失败记录保留错误信息用于排查
4. 记录不可删除，只能标记为取消

---

## Entity: Inpack (集运订单) - 已存在，需要扩展

**Purpose**: 集运订单主表，存储订单基本信息

**Modifications Needed**:
- 无需修改表结构
- 通过 WaybillRecord 表关联打印记录

**Query Methods**:
```php
// 获取订单的打印历史
public function getWaybillHistory() {
    return $this->hasMany('WaybillRecord', 'order_id');
}

// 获取最后一次打印记录
public function getLastWaybill() {
    return $this->hasOne('WaybillRecord', 'order_id')
        ->where('status', 1)
        ->order('created_at', 'desc');
}
```

---

## Entity: ExpressConfig (快递配置) - 可选，存储在 Setting 表

**Purpose**: 存储快递公司的 API 配置信息

**Storage**: 使用现有的 `yoshop_setting` 表，key 为 `express_config`

**Data Structure** (JSON):
```json
{
  "zhongtong": {
    "enabled": true,
    "api_url": "https://api.zhongtong.com",
    "app_key": "your_app_key",
    "app_secret": "your_app_secret",
    "customer_code": "your_customer_code",
    "timeout": 30
  },
  "shunfeng": {
    "enabled": true,
    "api_url": "https://api.sf-express.com",
    "app_key": "your_app_key",
    "app_secret": "your_app_secret",
    "customer_code": "your_customer_code",
    "timeout": 30
  }
}
```

**Access Method**:
```php
$config = Setting::getItem('express_config');
$zhongtongConfig = $config['zhongtong'];
```

---

## Database Migration Script

```sql
-- 创建面单打印记录表
CREATE TABLE IF NOT EXISTS `yoshop_waybill_record` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '主键ID',
  `order_id` int(11) unsigned NOT NULL COMMENT '订单ID',
  `order_sn` varchar(50) NOT NULL COMMENT '订单号',
  `express_type` tinyint(2) NOT NULL COMMENT '快递类型:1=中通,2=顺丰',
  `express_name` varchar(50) NOT NULL COMMENT '快递公司名称',
  `waybill_no` varchar(100) DEFAULT NULL COMMENT '运单号',
  `operation_type` tinyint(2) NOT NULL COMMENT '操作类型:1=打印,2=只下单',
  `status` tinyint(2) NOT NULL DEFAULT '1' COMMENT '状态:1=成功,2=失败,3=已取消',
  `api_request` text COMMENT 'API请求数据',
  `api_response` text COMMENT 'API响应数据',
  `error_message` varchar(500) DEFAULT NULL COMMENT '错误信息',
  `operator_id` int(11) unsigned NOT NULL COMMENT '操作人ID',
  `operator_name` varchar(50) NOT NULL COMMENT '操作人姓名',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT '10001' COMMENT '小程序ID',
  `created_at` datetime NOT NULL COMMENT '创建时间',
  `updated_at` datetime DEFAULT NULL COMMENT '更新时间',
  PRIMARY KEY (`id`),
  KEY `idx_order_id` (`order_id`),
  KEY `idx_waybill_no` (`waybill_no`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_wxapp_id` (`wxapp_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='面单打印记录表';
```

---

## Data Flow Diagram

```
┌─────────────────┐
│   Inpack        │
│   (订单表)       │
│  - id           │
│  - order_sn     │
│  - member_id    │
│  - address_id   │
│  - ...          │
└────────┬────────┘
         │ 1
         │
         │ N
         ↓
┌─────────────────┐
│ WaybillRecord   │
│ (打印记录表)     │
│  - id           │
│  - order_id     │◄─── 关联订单
│  - waybill_no   │
│  - express_type │
│  - status       │
│  - operator_id  │
│  - created_at   │
└─────────────────┘
```

---

## Query Examples

### 1. 获取订单的所有打印记录
```php
$records = WaybillRecord::where('order_id', $orderId)
    ->order('created_at', 'desc')
    ->select();
```

### 2. 获取某个运单号的记录
```php
$record = WaybillRecord::where('waybill_no', $waybillNo)
    ->find();
```

### 3. 统计今日打印数量
```php
$count = WaybillRecord::whereTime('created_at', 'today')
    ->where('status', 1)
    ->where('operation_type', 1)
    ->count();
```

### 4. 获取失败的打印记录
```php
$failedRecords = WaybillRecord::where('status', 2)
    ->whereTime('created_at', 'today')
    ->select();
```

### 5. 检查订单是否已打印
```php
$hasPrinted = WaybillRecord::where('order_id', $orderId)
    ->where('status', 1)
    ->where('operation_type', 1)
    ->count() > 0;
```

---

## Data Retention Policy

1. **保留期限**: 打印记录保留 1 年
2. **归档策略**: 超过 1 年的记录移至归档表
3. **清理脚本**: 定期清理归档数据（保留 3 年）
4. **备份**: 每日备份打印记录数据
