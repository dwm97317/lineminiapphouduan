# Technical Design: 推荐奖励系统配置视图增强

**Feature Branch**: `001-referral-config-view-enhancement`
**Created**: 2026-01-17
**Status**: Draft

## Architecture Overview

本功能主要涉及前端视图层的增强，不涉及后端逻辑的修改。主要改进现有的配置页面视图，增加统计面板、卡片视图、预览功能等。

### Technology Stack

- **Frontend**: PHP (ThinkPHP 5.0 模板引擎)
- **UI Framework**: Amaze UI 2.7
- **JavaScript**: jQuery 3.x
- **CSS**: Custom CSS + Amaze UI

### Component Structure

```
source/application/store/view/setting/referral/
├── config.php                    # 主配置页面（需要增强）
├── _statistics_panel.php         # 统计面板组件（新增）
├── _task_card.php                # 任务卡片组件（新增）
├── _reward_card.php              # 奖励卡片组件（新增）
├── _preview_modal.php            # 预览弹窗组件（新增）
└── _history_modal.php            # 历史记录弹窗组件（新增）
```

## Data Models

### 任务配置数据结构

```php
[
    'id' => 1,
    'task_type' => 'invite_success',  // 任务类型
    'config_name' => '成功邀请用户',   // 任务名称
    'role_type' => 'referrer',        // 角色类型：referrer/referee
    'is_enabled' => 1,                // 是否启用
    'is_required' => 1,               // 是否必须完成
    'task_params' => [                // 任务参数（JSON）
        'min_invites' => 1            // 最低邀请人数
    ]
]
```

### 奖励配置数据结构

```php
[
    'id' => 1,
    'config_name' => '一级推荐奖励',   // 配置名称
    'level' => 1,                     // 推荐级别
    'reward_type' => 1,               // 奖励类型：1=现金，2=积分，3=优惠券
    'reward_amount' => 50.00,         // 奖励金额/数量
    'reward_ratio' => 100.00,         // 奖励比例
    'is_enabled' => 1,                // 是否启用
    'expire_days' => 30,              // 有效期（天）
    'reward_params' => [              // 奖励参数（JSON）
        'min_withdraw' => 100,        // 最低提现金额（现金类型）
        'coupon_id' => 123,           // 优惠券ID（优惠券类型）
        'points_expire_days' => 90    // 积分有效期（积分类型）
    ]
]
```

### 统计数据结构

```php
[
    'total_tasks' => 6,               // 总任务数
    'enabled_tasks' => 4,             // 启用任务数
    'total_rewards' => 3,             // 总奖励配置数
    'enabled_rewards' => 2,           // 启用奖励配置数
    'max_levels' => 3,                // 最大推荐级数
    'referrer_tasks' => 3,            // 推荐人任务数
    'referee_tasks' => 3              // 被推荐人任务数
]
```

## API Contracts

### 获取配置统计信息

**Endpoint**: `GET /store/setting.referral/getConfigStats`

**Response**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "total_tasks": 6,
        "enabled_tasks": 4,
        "total_rewards": 3,
        "enabled_rewards": 2,
        "max_levels": 3,
        "referrer_tasks": 3,
        "referee_tasks": 3
    }
}
```

### 获取配置历史记录

**Endpoint**: `GET /store/setting.referral/getConfigHistory`

**Parameters**:
- `config_type`: 配置类型（system/task/reward）
- `page`: 页码（默认1）
- `limit`: 每页数量（默认20）

**Response**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "list": [
            {
                "id": 1,
                "config_type": "task",
                "config_id": 1,
                "old_value": "{...}",
                "new_value": "{...}",
                "operator_id": 1,
                "operator_name": "管理员",
                "create_time": "2026-01-17 10:00:00"
            }
        ],
        "total": 10,
        "page": 1,
        "limit": 20
    }
}
```

### 预览配置效果

**Endpoint**: `POST /store/setting.referral/previewConfig`

**Request**:
```json
{
    "config_type": "reward",
    "config_data": {
        "1": {
            "reward_type": 1,
            "reward_amount": 50,
            "reward_ratio": 100
        }
    }
}
```

**Response**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "preview_html": "<div>...</div>",
        "examples": [
            {
                "level": 1,
                "description": "一级推荐奖励",
                "amount": "50.00 泰铢"
            }
        ]
    }
}
```

## UI Components

### 1. 统计面板组件 (_statistics_panel.php)

**功能**: 显示系统配置的统计信息

**布局**:
```
┌─────────────────────────────────────────────────────────┐
│  [图标] 启用任务数        [图标] 奖励配置数              │
│         4/6                      2/3                     │
│                                                          │
│  [图标] 推荐级数          [图标] 推荐人任务              │
│         3级                      3个                     │
└─────────────────────────────────────────────────────────┘
```

**样式**:
- 使用 Amaze UI 的 `am-panel` 组件
- 4列网格布局（`am-u-sm-12 am-u-md-6 am-u-lg-3`）
- 每个统计项使用图标 + 数字 + 标签的形式
- 可点击跳转到对应的配置标签页

### 2. 任务卡片组件 (_task_card.php)

**功能**: 以卡片形式展示单个任务配置

**布局**:
```
┌─────────────────────────────────────────────────────────┐
│  [任务名称]                          [已启用] [必须完成] │
│  任务类型: invite_success                               │
│                                                          │
│  ☑ 启用    ☑ 必须完成                                   │
│                                                          │
│  参数配置:                                               │
│  最低邀请人数: [1] 人                                    │
└─────────────────────────────────────────────────────────┘
```

**样式**:
- 使用 Amaze UI 的 `am-panel` 组件
- 标题区域显示任务名称和状态标签
- 内容区域显示任务类型、启用状态、参数配置
- 状态标签使用不同颜色：启用=绿色，禁用=灰色，必须完成=红色

### 3. 奖励卡片组件 (_reward_card.php)

**功能**: 以卡片形式展示单个奖励配置

**布局**:
```
┌─────────────────────────────────────────────────────────┐
│  [💰] 一级推荐奖励                      [已启用]         │
│                                                          │
│  奖励类型: ● 现金  ○ 积分  ○ 优惠券                     │
│  奖励金额: [50.00] 泰铢                                  │
│  奖励比例: [100] %                                       │
│  有效期: [30] 天                                         │
│                                                          │
│  参数配置:                                               │
│  最低提现金额: [100] 泰铢                                │
└─────────────────────────────────────────────────────────┘
```

**样式**:
- 使用 Amaze UI 的 `am-panel` 组件
- 标题区域显示奖励图标、名称和状态标签
- 内容区域显示奖励类型、金额、比例、有效期、参数配置
- 不同奖励类型使用不同图标：现金=💰，积分=⭐，优惠券=🎫
- 禁用状态的卡片显示半透明效果

### 4. 预览弹窗组件 (_preview_modal.php)

**功能**: 预览配置效果

**布局**:
```
┌─────────────────────────────────────────────────────────┐
│  配置预览                                        [关闭]  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  任务流程图:                                             │
│  用户注册 → 完成首次充值 → 完成首次下单 → 获得奖励      │
│                                                          │
│  奖励计算示例:                                           │
│  • 一级推荐: 50.00 泰铢 (100%)                          │
│  • 二级推荐: 25.00 泰铢 (50%)                           │
│  • 三级推荐: 12.50 泰铢 (25%)                           │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

**样式**:
- 使用 Amaze UI 的 `am-modal` 组件
- 显示任务流程图和奖励计算示例
- 使用图表或流程图展示配置效果

### 5. 历史记录弹窗组件 (_history_modal.php)

**功能**: 查看配置历史记录

**布局**:
```
┌─────────────────────────────────────────────────────────┐
│  配置历史                                        [关闭]  │
├─────────────────────────────────────────────────────────┤
│                                                          │
│  2026-01-17 10:00:00  管理员  修改了任务配置            │
│  • 将"成功邀请用户"的最低邀请人数从 1 改为 3            │
│  [查看详情] [恢复此配置]                                 │
│                                                          │
│  2026-01-16 15:30:00  管理员  修改了奖励配置            │
│  • 将"一级推荐奖励"的金额从 30 改为 50                  │
│  [查看详情] [恢复此配置]                                 │
│                                                          │
└─────────────────────────────────────────────────────────┘
```

**样式**:
- 使用 Amaze UI 的 `am-modal` 组件
- 列表形式展示历史记录
- 每条记录显示时间、操作人、修改内容摘要
- 提供"查看详情"和"恢复此配置"按钮

## Implementation Plan

### Phase 1: 基础组件开发 (P1)

1. 创建统计面板组件 `_statistics_panel.php`
2. 创建任务卡片组件 `_task_card.php`
3. 创建奖励卡片组件 `_reward_card.php`
4. 修改主配置页面 `config.php`，集成新组件

### Phase 2: 预览功能开发 (P2)

1. 创建预览弹窗组件 `_preview_modal.php`
2. 实现预览逻辑（JavaScript）
3. 添加预览按钮到配置页面
4. 实现后端预览接口 `previewConfig`

### Phase 3: 历史记录功能开发 (P3)

1. 创建配置历史数据表 `referral_config_history`
2. 创建历史记录弹窗组件 `_history_modal.php`
3. 实现历史记录查询接口 `getConfigHistory`
4. 实现配置恢复功能
5. 在配置保存时自动记录历史

### Phase 4: 统计功能开发 (P2)

1. 实现统计数据计算逻辑
2. 创建统计数据接口 `getConfigStats`
3. 实现统计面板的数据绑定
4. 实现统计卡片的点击跳转功能

## Database Schema

### 配置历史表 (referral_config_history)

```sql
CREATE TABLE `referral_config_history` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT '历史记录ID',
  `wxapp_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '小程序ID',
  `config_type` varchar(20) NOT NULL DEFAULT '' COMMENT '配置类型：system/task/reward',
  `config_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '配置ID',
  `old_value` text COMMENT '修改前的值（JSON）',
  `new_value` text COMMENT '修改后的值（JSON）',
  `operator_id` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '操作人ID',
  `operator_name` varchar(50) NOT NULL DEFAULT '' COMMENT '操作人名称',
  `create_time` int(11) unsigned NOT NULL DEFAULT '0' COMMENT '创建时间',
  PRIMARY KEY (`id`),
  KEY `wxapp_id` (`wxapp_id`),
  KEY `config_type` (`config_type`),
  KEY `config_id` (`config_id`),
  KEY `create_time` (`create_time`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COMMENT='推荐系统配置历史表';
```

## JavaScript Interactions

### 统计面板点击跳转

```javascript
// 点击统计卡片跳转到对应标签页
$('.statistics-card').on('click', function() {
    var targetTab = $(this).data('target-tab');
    $('.am-tabs-nav a[href="#' + targetTab + '"]').trigger('click');
});
```

### 预览配置

```javascript
// 点击预览按钮
$('.btn-preview-config').on('click', function() {
    var configType = $(this).data('config-type');
    var formData = $('form[data-config-type="' + configType + '"]').serialize();
    
    $.ajax({
        url: '/store/setting.referral/previewConfig',
        type: 'POST',
        data: formData,
        success: function(response) {
            if (response.code === 1) {
                $('#preview-modal .modal-body').html(response.data.preview_html);
                $('#preview-modal').modal('open');
            }
        }
    });
});
```

### 查看历史记录

```javascript
// 点击查看历史按钮
$('.btn-view-history').on('click', function() {
    var configType = $(this).data('config-type');
    
    $.ajax({
        url: '/store/setting.referral/getConfigHistory',
        type: 'GET',
        data: { config_type: configType, page: 1, limit: 20 },
        success: function(response) {
            if (response.code === 1) {
                renderHistoryList(response.data.list);
                $('#history-modal').modal('open');
            }
        }
    });
});
```

### 恢复配置

```javascript
// 点击恢复配置按钮
$('.btn-restore-config').on('click', function() {
    var historyId = $(this).data('history-id');
    
    if (confirm('确定要恢复此配置吗？')) {
        $.ajax({
            url: '/store/setting.referral/restoreConfig',
            type: 'POST',
            data: { history_id: historyId },
            success: function(response) {
                if (response.code === 1) {
                    alert('配置恢复成功');
                    location.reload();
                } else {
                    alert('配置恢复失败：' + response.msg);
                }
            }
        });
    }
});
```

## CSS Styles

### 统计卡片样式

```css
.statistics-card {
    cursor: pointer;
    transition: all 0.3s ease;
    padding: 20px;
    text-align: center;
    border-radius: 8px;
    background: #fff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.statistics-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 4px 8px rgba(0,0,0,0.15);
}

.statistics-card .icon {
    font-size: 36px;
    color: #0e90d2;
    margin-bottom: 10px;
}

.statistics-card .value {
    font-size: 28px;
    font-weight: bold;
    color: #333;
    margin-bottom: 5px;
}

.statistics-card .label {
    font-size: 14px;
    color: #999;
}
```

### 任务卡片样式

```css
.task-card {
    margin-bottom: 15px;
    border-radius: 8px;
    overflow: hidden;
}

.task-card .card-header {
    background: #f5f5f5;
    padding: 15px;
    border-bottom: 1px solid #e5e5e5;
}

.task-card .task-name {
    font-size: 16px;
    font-weight: bold;
    color: #333;
    display: inline-block;
}

.task-card .task-status {
    float: right;
}

.task-card .status-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
    margin-left: 5px;
}

.task-card .status-badge.enabled {
    background: #5eb95e;
    color: #fff;
}

.task-card .status-badge.disabled {
    background: #999;
    color: #fff;
}

.task-card .status-badge.required {
    background: #dd514c;
    color: #fff;
}

.task-card .card-body {
    padding: 15px;
}

.task-card .task-type {
    color: #999;
    font-size: 14px;
    margin-bottom: 15px;
}

.task-card .task-params {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e5e5e5;
}
```

### 奖励卡片样式

```css
.reward-card {
    margin-bottom: 15px;
    border-radius: 8px;
    overflow: hidden;
    transition: all 0.3s ease;
}

.reward-card.disabled {
    opacity: 0.5;
}

.reward-card .card-header {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    color: #fff;
    padding: 15px;
}

.reward-card .reward-icon {
    font-size: 24px;
    margin-right: 10px;
}

.reward-card .reward-name {
    font-size: 16px;
    font-weight: bold;
    display: inline-block;
}

.reward-card .reward-level {
    float: right;
    background: rgba(255,255,255,0.3);
    padding: 3px 10px;
    border-radius: 12px;
    font-size: 12px;
}

.reward-card .card-body {
    padding: 15px;
    background: #fff;
}

.reward-card .reward-type-selector {
    margin-bottom: 15px;
}

.reward-card .reward-params {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #e5e5e5;
}
```

## Testing Strategy

### Unit Tests

- 测试统计数据计算的准确性
- 测试配置历史记录的保存和查询
- 测试配置恢复功能的正确性

### Integration Tests

- 测试统计面板与配置数据的集成
- 测试任务卡片与表单提交的集成
- 测试奖励卡片与表单提交的集成
- 测试预览功能与后端接口的集成
- 测试历史记录功能与后端接口的集成

### UI Tests

- 测试统计面板的显示效果
- 测试任务卡片的显示效果
- 测试奖励卡片的显示效果
- 测试预览弹窗的显示效果
- 测试历史记录弹窗的显示效果
- 测试响应式布局在不同屏幕尺寸下的表现

### Performance Tests

- 测试配置页面的加载时间（目标：<2秒）
- 测试配置保存的响应时间（目标：<1秒）
- 测试历史记录查询的响应时间（目标：<1秒）
- 测试预览功能的响应时间（目标：<1秒）

## Security Considerations

- 配置修改需要管理员权限验证
- 配置历史记录需要记录操作人信息
- 配置恢复需要二次确认
- 所有用户输入需要进行数据验证和过滤
- 防止 XSS 攻击：对用户输入进行 HTML 转义
- 防止 CSRF 攻击：使用 ThinkPHP 的 CSRF 令牌验证

## Performance Optimization

- 使用 AJAX 异步加载统计数据，避免阻塞页面渲染
- 使用缓存存储配置数据，减少数据库查询
- 使用分页加载历史记录，避免一次性加载大量数据
- 使用 CSS 动画代替 JavaScript 动画，提高性能
- 使用事件委托减少事件监听器数量

## Deployment Considerations

- 需要执行数据库迁移脚本创建配置历史表
- 需要清除模板缓存以应用新的视图文件
- 需要更新静态资源（CSS、JavaScript）
- 建议在低峰期进行部署，避免影响用户使用
- 部署后需要进行功能测试，确保所有功能正常工作
