# 账单系统API接口文档

## 概述

本文档描述账单系统的所有API接口。

**基础URL**: `/store/`

**响应格式**:
```json
{
  "code": 1,           // 1成功，0失败
  "msg": "操作成功",
  "data": {}           // 返回数据
}
```

---

## 一、账单管理接口

### 1.1 生成账单

**接口**: `POST /package.statement/create`

**请求参数**:
```json
{
  "package_ids": [1, 2, 3],  // 订单ID数组
  "member_id": 31398          // 客户ID
}
```

**响应示例**:
```json
{
  "code": 1,
  "msg": "账单生成成功",
  "data": {
    "statement_id": 123,
    "statement_no": "ST20260228001",
    "total_amount": 2300.00,
    "excel_path": "./uploads/statements/ST20260228001_31398.xlsx"
  }
}
```

---

### 1.2 账单列表

**接口**: `GET /package.statement/list`

**请求参数**:
- `page`: 页码（默认1）
- `page_size`: 每页数量（默认20）
- `member_id`: 客户ID（可选）
- `pay_status`: 支付状态（1未支付，2已支付）
- `status`: 账单状态（1正常，2已作废）
- `start_date`: 开始日期（可选）
- `end_date`: 结束日期（可选）
- `keyword`: 关键词搜索（账单编号或客户姓名）

**响应示例**:
```json
{
  "code": 1,
  "msg": "",
  "data": {
    "list": [
      {
        "id": 123,
        "statement_no": "ST20260228001",
        "member_id": 31398,
        "member_name": "张三",
        "total_packages": 5,
        "total_weight": 50.5,
        "total_amount": 2323.00,
        "pay_status": 1,
        "status": 1,
        "create_time": "2026-02-28 10:30:00"
      }
    ],
    "total": 10,
    "page": 1,
    "page_size": 20
  }
}
```

---

### 1.3 账单详情

**接口**: `GET /package.statement/detail`

**请求参数**:
- `statement_id`: 账单ID

**响应示例**:
```json
{
  "code": 1,
  "msg": "",
  "data": {
    "statement": {
      "id": 123,
      "statement_no": "ST20260228001",
      "member_name": "张三",
      "total_packages": 5,
      "total_amount": 2323.00,
      "pay_status": 1
    },
    "packages": [
      {
        "id": 1,
        "package_no": "P001",
        "weight": 10.0,
        "unit_price": 46.00,
        "amount": 460.00
      }
    ]
  }
}
```

---

### 1.4 标记为已支付

**接口**: `POST /package.statement/markPaid`

**请求参数**:
```json
{
  "statement_id": 123,
  "remark": "银行转账已收款"
}
```

**响应示例**:
```json
{
  "code": 1,
  "msg": "操作成功",
  "data": {
    "success": true,
    "success_count": 5,
    "failed_count": 0,
    "failed_ids": []
  }
}
```

---

### 1.5 作废账单

**接口**: `POST /package.statement/void`

**请求参数**:
```json
{
  "statement_id": 123
}
```

**响应示例**:
```json
{
  "code": 1,
  "msg": "账单已作废"
}
```

---

### 1.6 下载Excel

**接口**: `GET /package.statement/downloadExcel`

**请求参数**:
- `statement_id`: 账单ID

**响应**: 直接下载Excel文件

---

### 1.7 重新生成Excel

**接口**: `POST /package.statement/regenerateExcel`

**请求参数**:
```json
{
  "statement_id": 123
}
```

**响应示例**:
```json
{
  "code": 1,
  "msg": "Excel重新生成成功",
  "data": {
    "excel_path": "./uploads/statements/ST20260228001_31398.xlsx"
  }
}
```

---

### 1.8 获取统计数据

**接口**: `GET /package.statement/statistics`

**请求参数**:
- `start_date`: 开始日期（可选）
- `end_date`: 结束日期（可选）
- `member_id`: 客户ID（可选）

**响应示例**:
```json
{
  "code": 1,
  "msg": "",
  "data": {
    "total_count": 100,
    "total_amount": 46000.00,
    "paid_count": 80,
    "paid_amount": 36800.00,
    "unpaid_count": 15,
    "unpaid_amount": 6900.00,
    "void_count": 5
  }
}
```

---

## 二、财务配置接口

### 2.1 保存配置

**接口**: `POST /finance.config/save`

**请求参数（固定单价）**:
```json
{
  "member_id": 31398,
  "price_type": 1,
  "unit_price": 48.00,
  "status": 1
}
```

**请求参数（阶梯价格）**:
```json
{
  "member_id": 31398,
  "price_type": 2,
  "price_tier_json": {
    "tiers": [
      {"min": 0, "max": 10, "price": 50},
      {"min": 10, "max": null, "price": 46}
    ]
  },
  "unit_price": 46.00,
  "status": 1
}
```

**请求参数（自定义公式）**:
```json
{
  "member_id": 31398,
  "price_type": 5,
  "price_formula": "{weight} * 46 + 10",
  "status": 1
}
```

**响应示例**:
```json
{
  "code": 1,
  "msg": "配置保存成功",
  "data": {
    "id": 10,
    "member_id": 31398,
    "price_type": 1,
    "unit_price": 48.00
  }
}
```

---

### 2.2 获取配置

**接口**: `GET /finance.config/get`

**请求参数**:
- `member_id`: 客户ID（可选，不传则获取全局配置）

**响应示例**:
```json
{
  "code": 1,
  "msg": "",
  "data": {
    "id": 10,
    "member_id": 31398,
    "price_type": 1,
    "unit_price": 48.00,
    "status": 1
  }
}
```

---

### 2.3 获取配置列表

**接口**: `GET /finance.config/list`

**响应示例**:
```json
{
  "code": 1,
  "msg": "",
  "data": {
    "list": [
      {
        "id": 1,
        "member_id": null,
        "price_type": 1,
        "unit_price": 46.00
      },
      {
        "id": 2,
        "member_id": 31398,
        "price_type": 2,
        "price_tier_json": {...}
      }
    ]
  }
}
```

---

### 2.4 删除配置

**接口**: `POST /finance.config/delete`

**请求参数**:
```json
{
  "config_id": 10
}
```

**响应示例**:
```json
{
  "code": 1,
  "msg": "配置已删除"
}
```

---

### 2.5 导入历史单价

**接口**: `POST /finance.config/importHistoryPrice`

**请求参数**: 
- `file`: 文件（TXT或Excel）

**响应示例**:
```json
{
  "code": 1,
  "msg": "导入成功：10 条，失败：2 条",
  "data": {
    "success_count": 10,
    "failed_count": 2,
    "errors": ["第3行: 单价必须大于0"]
  }
}
```

---

### 2.6 上传文件

**接口**: `POST /finance.config/upload`

**请求参数**:
- `file`: 文件
- `type`: 类型（qrcode_alipay/qrcode_wechat/logo）
- `member_id`: 客户ID

**响应示例**:
```json
{
  "code": 1,
  "msg": "上传成功",
  "data": {
    "file_path": "./uploads/qrcode/alipay_31398.png"
  }
}
```

---

### 2.7 验证公式

**接口**: `POST /finance.config/validateFormula`

**请求参数**:
```json
{
  "formula": "{weight} * 46 + 10"
}
```

**响应示例**:
```json
{
  "code": 1,
  "msg": "公式有效",
  "data": {
    "test_result": 470.00
  }
}
```

---

### 2.8 保存模板

**接口**: `POST /finance.config/saveTemplate`

**请求参数**:
```json
{
  "template_name": "默认模板",
  "title": "集运订单对账单",
  "logo_path": "./uploads/logo/logo_31398.png",
  "alipay_qr_path": "./uploads/qrcode/alipay_31398.png",
  "wechat_qr_path": "./uploads/qrcode/wechat_31398.png",
  "notice_text": "请核对账单信息",
  "is_default": 1
}
```

**响应示例**:
```json
{
  "code": 1,
  "msg": "模板保存成功",
  "data": {
    "id": 5,
    "template_name": "默认模板"
  }
}
```

---

### 2.9 获取模板列表

**接口**: `GET /finance.config/templateList`

**响应示例**:
```json
{
  "code": 1,
  "msg": "",
  "data": {
    "list": [
      {
        "id": 1,
        "template_name": "默认模板",
        "is_default": 1
      }
    ]
  }
}
```

---

## 三、错误码说明

| 错误码 | 说明 |
|--------|------|
| 1 | 成功 |
| 0 | 失败 |

**常见错误消息**:
- "请选择订单"
- "只能选择同一个客户的订单"
- "订单已归档"
- "账单不存在"
- "已支付的账单不能作废"
- "公式语法错误"

---

## 四、使用示例

### 4.1 生成账单流程

```javascript
// 1. 选择订单
const packageIds = [1, 2, 3];
const memberId = 31398;

// 2. 调用生成接口
fetch('/store/package.statement/create', {
  method: 'POST',
  body: JSON.stringify({
    package_ids: packageIds,
    member_id: memberId
  })
})
.then(res => res.json())
.then(data => {
  if (data.code === 1) {
    console.log('账单生成成功:', data.data.statement_no);
  }
});
```

### 4.2 查询账单列表

```javascript
fetch('/store/package.statement/list?page=1&pay_status=1')
  .then(res => res.json())
  .then(data => {
    console.log('未支付账单:', data.data.list);
  });
```

---

**文档版本**: 1.0  
**更新时间**: 2026-02-28
