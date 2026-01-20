# 分销系统与分销等级功能技术文档

## 1. 概述
本文档旨在深入阐述现有分销系统（Dealer）的业务逻辑、数据流向及底层代码实现，特别关注**分销订单**与**分销等级**功能的现状与集成点。该文档可作为AI助手或开发人员进行功能扩展（如修复等级权益、实现自动升级）的参考基准。

## 2. 核心数据模型

### 2.1 分销订单 (`dealer_order`)
记录订单产生的分销佣金预估及结算状态。

| 字段名 | 类型 | 描述 |
| :--- | :--- | :--- |
| `order_id` | int | 关联主订单ID (`order` 表) |
| `user_id` | int | 购买用户ID |
| `first_user_id` | int | 一级分销商用户ID (直接推荐人) |
| `second_user_id` | int | 二级分销商用户ID |
| `third_user_id` | int | 三级分销商用户ID |
| `first_money` | decimal | 一级分销商佣金 |
| `second_money` | decimal | 二级分销商佣金 |
| `third_money` | decimal | 三级分销商佣金 |
| `is_settled` | tinyint | 结算状态 (0: 未结算, 1: 已结算) |
| `settle_time` | int | 结算时间戳 |
| `order_price` | decimal | 订单实际计算佣金的金额 |

### 2.2 分销用户 (`dealer_user`)
关联用户表，存储分销商特定信息。

| 字段名 | 类型 | 描述 |
| :--- | :--- | :--- |
| `user_id` | int | 关联用户ID |
| `rating_id` | int | **分销等级ID** (关联 `dealer_rating`) |
| `money` | decimal | 可提现佣金余额 |
| `freeze_money` | decimal | 冻结佣金 (已申请提现但未打款) |
| `first_num` | int | 一级团队人数 |
| `referee_id` | int | 推荐人ID |

### 2.3 分销等级 (`dealer_rating`)
定义分销商等级及其权益（注意：部分逻辑目前仅存在于数据库和配置中，未完全生效）。

| 字段名 | 类型 | 描述 |
| :--- | :--- | :--- |
| `rating_id` | int | 主键 |
| `name` | varchar | 等级名称 |
| `weight` | int | 权重 (数字越大等级越高) |
| `upgrade` | json | **升级条件** (如: `{"expend_money": 1000}`) |
| `setting` | json | **佣金权益** (如: `{"first_money": 10, ...}`) |

---

## 3. 业务逻辑流程

### 3.1 分销订单创建流程
**触发点**: 用户下单支付成功后，调用 `Checkout` 服务的 `createOrderEvent`。

1. **入口**: `app\api\service\order\Checkout::createOrderEvent` 调用 `DealerOrderModel::createOrder`。
2. **逻辑位置**: `app\api\model\dealer\Order::createOrder`
3. **关键步骤**:
    *   **检查开关**: 验证后台分销功能是否开启。
    *   **溯源上级**: 调用 `getDealerUserId` 获取当前的 1级、2级、3级 分销商ID。
        *   支持“分销商自购”逻辑 (`self_buy`)。
    *   **佣金计算**: 调用 `getCapitalByOrder` 计算各级佣金。
        *   **现状**: 目前仅支持 **A. 全局统一比例** 或 **B. 商品单独设置比例**。
        *   **缺失**: 代码中**尚未**引入 `dealer_rating` 中的佣金比例配置。即无论分销商等级如何，佣金比例目前是相同的。
    *   **落库**: 插入 `dealer_order` 记录，初始 `is_settled = 0`。

### 3.2 佣金结算流程
**触发点**: 计划任务或后台触发，通常由 `Task` 模块驱动。

1. **入口**: `app\task\behavior\DealerOrder::run`
2. **逻辑**:
    *   查询 `dealer_order` 中 `is_settled=0` 且主订单 `order_status=30` (已完成) 的记录。
    *   **时间检查**: 检查 `receipt_time` (收货时间) + `settle_days` (配置的结算天数) 是否小于当前时间。
    *   **发放佣金**: 调用 `common\model\dealer\Order::grantMoney`。
        *   **重算**: 再次调用 `getCapitalByOrder` 确保金额准确 (业务上通常应以创建时为准，但此处逻辑为重算)。
        *   **入账**: 调用 `User::grantMoney` 增加分销商 `money` 余额。
        *   **日志**: 写入 `dealer_capital` 流水记录。
        *   **更新状态**: 更新 `dealer_order` 为 `is_settled=1`。

### 3.3 分销等级 (Rating) 现状分析
**当前状态**: **功能不完整**。

*   **配置端**: 后台 (`store\controller\apps\dealer\Rating`) 支持增删改查等级，并配置“升级条件”和“佣金比例”。
*   **应用端**:
    *   **佣金计算**: `common\model\dealer\Order` 中的计算逻辑完全忽略了 `dealer_user.rating_id`，仅使用全局/商品设置。
    *   **自动升级**: 代码库中**未发现**基于 `upgrade` 字段 (如 `expend_money`) 自动提升用户分销等级的逻辑。

---

## 4. AI 对接与开发指南

若 AI 需要接管或修复此系统，请关注以下接口与逻辑：

### 4.1 核心类文件路径
*   **计算与实体**: `source/application/common/model/dealer/Order.php`
*   **订单创建**: `source/application/api/model/dealer/Order.php`
*   **结算任务**: `source/application/task/behavior/DealerOrder.php`
*   **分销设置**: `source/application/common/model/dealer/Setting.php`

### 4.2 待实现功能 (TODO)
为了完善分销等级系统，建议执行以下变更：

1.  **修改佣金计算逻辑**:
    *   修改 `common\model\dealer\Order::calculateGoodsCapital` 方法。
    *   在计算前读取对应分销商 (`first_user_id` 等) 的 `rating_id`。
    *   优先使用 `dealer_rating` 中配置的 `setting.first_money` 等比例覆盖全局设置。

2.  **实现等级自动升级**:
    *   **监听点**: 在 `User::grantMoney` (发放佣金时) 或 `Order::complete` (订单完成时)。
    *   **逻辑**:
        *   统计该分销商的累计佣金 (`expend_money` 或累加 `dealer_order` 金额)。
        *   查询所有 `dealer_rating`，找到满足条件 (`upgrade.expend_money <= total`) 的最高权重等级。
        *   更新 `dealer_user` 表的 `rating_id`。
        *   记录升级日志。

### 4.3 常用查询语句 (伪代码)
*   **查询某用户的分销订单**:
    ```php
    // api/model/dealer/Order.php
    $model->getList($user_id, $is_settled);
    ```
*   **获取某用户的团队**:
    ```php
     Referee::getList($user_id, $level);
    ```
