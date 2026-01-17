# 推荐奖励系统 - 数据库迁移指南

## 概述

本目录包含推荐奖励系统的数据库迁移文件和初始数据填充脚本。

## 文件说明

### 迁移文件

- **migrations/20260117_create_referral_system_tables.php**
  - ThinkPHP Phinx 迁移类
  - 创建7个推荐系统相关表
  - 包含完整的表结构和索引定义

### 数据填充文件

- **seeds/ReferralSystemSeeder.php**
  - 初始配置数据填充
  - 包含系统配置、任务配置、奖励配置

### SQL文件

- **../create_referral_system_tables.sql**
  - 纯SQL迁移脚本
  - 可直接在MySQL中执行
  - 包含表结构和初始数据

### 执行脚本

- **../execute_referral_migration.php**
  - PHP执行脚本
  - 自动执行SQL迁移
  - 包含详细的执行日志和验证

- **../verify_referral_tables.php**
  - 表结构验证脚本
  - 检查所有表和索引
  - 显示初始配置数据

## 使用方法

### 方法1: 使用ThinkPHP迁移工具 (推荐)

如果项目已配置ThinkPHP Phinx迁移工具:

```bash
# 执行迁移
php think migrate:run

# 填充初始数据
php think seed:run --seed ReferralSystemSeeder

# 回滚迁移
php think migrate:rollback
```

### 方法2: 使用PHP执行脚本 (已完成)

```bash
# 进入后端目录
cd Lineminiapp

# 执行迁移
php execute_referral_migration.php

# 验证表结构
php verify_referral_tables.php
```

### 方法3: 直接执行SQL文件

```bash
# 使用MySQL命令行
mysql -h 103.119.1.84 -u xinsuju -p xinsuju < create_referral_system_tables.sql

# 或使用phpMyAdmin导入SQL文件
```

## 创建的表

### 1. yoshop_user_referral_code
用户推荐码表,存储每个用户的专属推荐码和统计数据。

**关键字段**:
- `user_id`: 用户ID (唯一)
- `referral_code`: 推荐码 (唯一, 6-8位)
- `share_count`: 分享次数
- `register_count`: 注册人数
- `success_count`: 成功推荐数
- `total_reward`: 累计奖励金额

### 2. yoshop_referral_relation
推荐关系表,支持多级推荐关系。

**关键字段**:
- `referrer_user_id`: 推荐人ID
- `referee_user_id`: 被推荐人ID (唯一)
- `level`: 推荐级别 (1=一级, 2=二级...)
- `parent_relation_id`: 上级推荐关系ID
- `status`: 状态 (1=待完成, 2=已完成, 3=已失效)
- `referrer_task_status`: 推荐人任务状态
- `referee_task_status`: 被推荐人任务状态

### 3. yoshop_referral_reward
推荐奖励记录表,记录每次奖励发放。

**关键字段**:
- `relation_id`: 推荐关系ID
- `user_id`: 获得奖励的用户ID
- `user_type`: 用户类型 (1=推荐人, 2=被推荐人)
- `reward_type`: 奖励类型 (1=现金, 2=积分, 3=优惠券)
- `reward_amount`: 奖励金额/数量
- `status`: 状态 (1=待发放, 2=已发放, 3=已回收)

### 4. yoshop_referral_task_config
推荐任务配置表,配置推荐人和被推荐人需要完成的任务。

**关键字段**:
- `config_name`: 配置名称
- `user_type`: 用户类型 (1=推荐人, 2=被推荐人)
- `task_type`: 任务类型 (register/first_recharge/first_order等)
- `task_params`: 任务参数 (JSON格式)
- `is_required`: 是否必须完成

### 5. yoshop_referral_reward_config
推荐奖励配置表,配置不同级别的奖励规则。

**关键字段**:
- `config_name`: 配置名称
- `level`: 推荐级别
- `user_type`: 用户类型
- `reward_type`: 奖励类型
- `reward_amount`: 奖励金额/数量
- `reward_ratio`: 奖励比例 (用于多级推荐)
- `expire_days`: 有效期 (天数, NULL=永久)

### 6. yoshop_referral_system_config
推荐系统配置表,全局系统配置。

**关键字段**:
- `config_key`: 配置键 (唯一)
- `config_value`: 配置值
- `config_type`: 配置类型 (string/int/json等)
- `description`: 配置说明

**初始配置项**:
- `max_referral_levels`: 最大推荐级数 (默认: 1)
- `referral_code_length`: 推荐码长度 (默认: 6)
- `referral_limit_enabled`: 是否启用推荐上限 (默认: 0)
- `referral_limit_per_month`: 每月推荐上限 (默认: 100)
- `expire_days`: 推荐关系失效天数 (默认: 30)
- `leaderboard_enabled`: 是否启用排行榜 (默认: 1)
- `leaderboard_top_count`: 排行榜显示人数 (默认: 100)
- `anti_fraud_enabled`: 是否启用防刷机制 (默认: 1)

### 7. yoshop_referral_leaderboard
推荐排行榜表,存储排行榜数据。

**关键字段**:
- `period_type`: 周期类型 (daily/weekly/monthly)
- `period_date`: 周期日期
- `user_id`: 用户ID
- `referral_count`: 推荐人数
- `success_count`: 成功推荐数
- `rank`: 排名
- `reward_amount`: 排行榜奖励金额

## 初始配置数据

### 系统配置
- 推荐级数: 1级 (可后台调整为2级、3级)
- 推荐码长度: 6位
- 推荐上限: 未启用
- 失效天数: 30天
- 排行榜: 启用
- 防刷机制: 启用

### 任务配置
1. **推荐人任务**: 邀请成功
2. **被推荐人任务**: 
   - 完成注册
   - 完成首次充值 (最低100元)

### 奖励配置
1. **推荐人奖励**: 50元现金 (永久有效)
2. **被推荐人奖励**: 30元现金 (永久有效)

## 索引说明

所有表都包含优化的索引配置:

- **唯一索引**: 防止重复数据
  - `user_referral_code.user_id`
  - `user_referral_code.referral_code`
  - `referral_relation.referee_user_id`
  - `referral_system_config.config_key`
  - `referral_leaderboard.(period_type, period_date, user_id)`

- **普通索引**: 优化查询性能
  - `referral_relation.referrer_user_id`
  - `referral_relation.status`
  - `referral_relation.parent_relation_id`
  - `referral_reward.user_id`
  - `referral_reward.status`
  - 所有表的 `create_time`

## 验证迁移结果

执行迁移后,可以通过以下方式验证:

```bash
# 使用验证脚本
php verify_referral_tables.php

# 或手动检查
mysql -h 103.119.1.84 -u xinsuju -p xinsuju -e "SHOW TABLES LIKE 'yoshop_referral%';"
```

预期输出:
```
✓ 用户推荐码表 (yoshop_user_referral_code)
✓ 推荐关系表 (yoshop_referral_relation)
✓ 推荐奖励记录表 (yoshop_referral_reward)
✓ 推荐任务配置表 (yoshop_referral_task_config)
✓ 推荐奖励配置表 (yoshop_referral_reward_config)
✓ 推荐系统配置表 (yoshop_referral_system_config)
✓ 推荐排行榜表 (yoshop_referral_leaderboard)
```

## 回滚迁移

如果需要回滚迁移:

### 使用ThinkPHP迁移工具
```bash
php think migrate:rollback
```

### 手动回滚
```sql
DROP TABLE IF EXISTS `yoshop_referral_leaderboard`;
DROP TABLE IF EXISTS `yoshop_referral_system_config`;
DROP TABLE IF EXISTS `yoshop_referral_reward_config`;
DROP TABLE IF EXISTS `yoshop_referral_task_config`;
DROP TABLE IF EXISTS `yoshop_referral_reward`;
DROP TABLE IF EXISTS `yoshop_referral_relation`;
DROP TABLE IF EXISTS `yoshop_user_referral_code`;
```

## 注意事项

1. **备份数据库**: 执行迁移前请备份数据库
2. **检查权限**: 确保数据库用户有CREATE TABLE权限
3. **表前缀**: 所有表使用 `yoshop_` 前缀
4. **字符集**: 使用 `utf8mb4` 字符集
5. **时间戳**: 使用Unix时间戳 (整数类型)

## 下一步

迁移完成后,可以继续执行:

1. **阶段2**: 后端核心服务开发
   - 创建推荐码生成服务
   - 创建推荐关系服务
   - 创建任务验证服务
   - 创建奖励发放服务

2. **阶段3**: 后端API开发
   - 实现前端API接口
   - 实现后台管理API接口

3. **阶段4**: 前端页面开发
   - 创建邀请好友页面
   - 创建推荐记录页面
   - 创建排行榜页面

## 技术支持

如有问题,请参考:
- 设计文档: `zalo_mini_app-master/.kiro/specs/referral-reward-system/design.md`
- 任务列表: `zalo_mini_app-master/.kiro/specs/referral-reward-system/tasks.md`
- 需求文档: `zalo_mini_app-master/.kiro/specs/referral-reward-system/requirements.md`
