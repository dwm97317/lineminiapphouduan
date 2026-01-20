# 分销中心 (Distribution Center) API 对接文档

## 1. 概述
分销中心模块主要用于管理分销商的分销业务，包括申请成为分销商、查看团队成员、查看分销佣金、提现以及获取推广素材（海报/链接）。

**Base URL**: `/api/user/`

## 2. 鉴权说明
所有接口均需要通过 Header 传递用户 Token。
```
token: <USER_TOKEN>
```

---

## 3. API 列表

### 3.1 分销中心首页 (Dashboard)
获取分销商的首页数据，包括佣金概览、页面样式配置等。

- **接口地址**: `dealer.user/center`
- **请求方式**: `GET`
- **请求参数**: 无
- **返回示例**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "is_dealer": true,          // 是否为分销商
        "user": {                   // 当前用户信息
            "user_id": 10001,
            "nickName": "测试用户",
            "avatarUrl": "http://..."
        },
        "dealer": {                 // 分销商账户信息
            "user_id": 10001,
            "money": "500.00",      // 可提现佣金
            "freeze_money": "100.00", // 待结算佣金
            "total_money": "2000.00", // 已提现佣金
            "first_num": 10,        // 一级成员数
            "second_num": 5,        // 二级成员数
            "third_num": 2          // 三级成员数
        },
        "words": { ... },           // 页面自定义文案配置 (如 "我的团队", "佣金" 等词语的替换)
        "background": "http://..."  // 页面背景图 URL
    }
}
```

### 3.2 申请成为分销商
提交申请信息。只有在满足“填写申请信息”的条件下才需调用。

- **接口地址**: `dealer.apply/submit`
- **请求方式**: `POST`
- **请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| name | string | 是 | 真实姓名 |
| mobile | string | 是 | 手机号码 |

- **返回示例**:
```json
{
    "code": 1,
    "msg": "申请成功",
    "data": []
}
```

### 3.3 获取申请状态
查询当前用户是否在申请中，以及获取申请页面的配置信息（协议、背景图等）。

- **接口地址**: `dealer.user/apply`
- **请求方式**: `GET`
- **请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| referee_id | int | 否 | 推荐人ID (用于回显推荐人昵称) |

- **返回示例**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "is_dealer": false,         // 是否已经是分销商
        "is_applying": true,        // 是否正在审核中
        "referee_name": "张三",      // 推荐人昵称
        "license": "协议内容...",    // 分销商申请协议
        "words": { ... },           // 页面文案
        "background": "http://..."  // 背景图
    }
}
```

### 3.4 获取推广链接
获取带有当前用户身份标识的推广链接。

- **接口地址**: `dealer/inviteLink`
- **请求方式**: `GET`
- **请求参数**: 无
- **返回示例**:
```json
{
    "code": 1,
    "msg": "success",
    "data": "http://domain.com/html5/pages/login/index?key=<ENCRYPTED_KEY>"
}
```

### 3.5 获取推广海报 (二维码)
- **接口地址**: `dealer.qrcode/poster`
- **请求方式**: `GET`
- **请求参数**: 无
- **返回示例**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "qrcode": "http://.../poster.png", // 合成好的海报图片地址
        "words": { ... }
    }
}
```

### 3.6 我的团队
查询下线团队成员列表。

- **接口地址**: `dealer.team/lists`
- **请求方式**: `GET`
- **请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| level | int | 是 | 查询层级: -1=全部, 1=一级, 2=二级, 3=三级 |
| filter | string | 否 | 筛选范围: all=全部, today=今日新增, week=本周新增 |

- **返回示例**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "dealer": { ... }, // 当前分销商信息
        "list": {
            "data": [
                {
                    "user_id": 10002,
                    "nickName": "下线A",
                    "avatarUrl": "...",
                    "create_time": "2023-01-01 12:00:00",
                    "order": {
                        "num": 5,           // 该下线贡献的订单数
                        "all_price": 500.00, // 贡献的订单总额
                        "income": 50.00      // 贡献的佣金总额
                    }
                }
            ],
            "total": 1,
            "per_page": 15
        },
        "setting": { ... } // 基础分销设置
    }
}
```

### 3.7 分销订单列表
查询自己获得佣金的订单记录。

- **接口地址**: `dealer.order/lists`
- **请求方式**: `GET`
- **请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| settled | int | 否 | 结算状态: -1=全部, 0=未结算, 1=已结算 |

- **返回示例**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "list": {
            "data": [
                {
                    "order_id": 20001,
                    "first_money": "10.00",  // 获得的佣金金额
                    "is_settled": 1,         // 1=已结算, 0=未结算
                    "order_master": {        // 关联的原始订单信息
                        "order_no": "202301010001",
                        "pay_price": "100.00",
                        "user": { "nickName": "购买者昵称" }
                    }
                }
            ]
        }
    }
}
```

### 3.8 提现申请
提交佣金提现申请。

- **接口地址**: `dealer.withdraw/submit`
- **请求方式**: `POST`
- **请求参数**:

| 参数名 | 类型 | 必填 | 说明 |
| :--- | :--- | :--- | :--- |
| money | float | 是 | 提现金额 (必须大于最低提现额度) |
| pay_type | int | 是 | 提现方式: 10=微信, 20=支付宝, 30=银行卡, 40=余额 |
| alipay_name | string | 条件 | 支付宝姓名 (pay_type=20时必填) |
| alipay_account | string | 条件 | 支付宝账号 (pay_type=20时必填) |
| bank_name | string | 条件 | 银行名称 (pay_type=30时必填) |
| bank_account | string | 条件 | 银行卡号 (pay_type=30时必填) |
| bank_card | string | 条件 | 开户行 (pay_type=30时必填) |

### 3.9 提现明细 / 提现页面信息
获取提现页面所需的账户信息、配置信息，或查询历史提现记录。

- **接口地址**: `dealer.withdraw/lists` (注意：此接口通常用于列表，但部分页面也复用它或 `withdraw` 方法获取基础信息)
- **补充接口**: `dealer.withdraw/withdraw` (获取提现页面基础信息，如余额、支付方式配置)

**`dealer.withdraw/withdraw` 返回**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "dealer": { "money": "500.00" }, // 可提现金额
        "settlement": {                 // 结算配置
            "min_money": "10.00",       // 最低提现金额
            "pay_type": [10, 30]        // 支持的提现方式
        },
        "payenum": { "10": "微信", "30": "银行卡" }
    }
}
```

---

## 4. 业务逻辑说明

### 4.1 关系绑定 (团队组建)
- **触发条件**: 用户点击带有 `referee_id` 参数的推广链接或海报进入小程序/H5。
- **绑定规则**:
    1. 仅限新用户，或尚未绑定上级的用户。
    2. 一旦绑定，关系永久有效（除非后台人工解绑）。
    3. 支持最多3级关系链（User -> Dealer1 -> Dealer2 -> Dealer3）。

### 4.2 佣金结算
- **未结算**: 订单支付成功后，佣金记录生成，状态为 `0 (未结算)`。此时佣金不可提现。
- **已结算**: 订单完成（收货）后，经过后台设置的 `settle_days` (结算天数)，系统自动将状态转为 `1 (已结算)`，并将金额计入分销商的 `money` (可提现余额)。
- **已失效**: 若订单发生退款或取消，佣金记录标记为失效。

### 4.3 提现流程
1. **用户申请**: 填写金额和账户信息。
2. **后台审核**: 管理员在后台审核申请。
3. **打款**:
    - **余额提现**: 审核通过后自动加到用户余额。
    - **微信提现**: 若开启企业付款到零钱，审核通过后自动打款；否则需线下打款并手动确认。
    - **线下转账**: 支付宝/银行卡均为线下打款，管理员确认后在后台标记为“已打款”。
