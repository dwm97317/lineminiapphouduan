# 代理商功能与增值服务动态追加实施计划

本计划旨在明确 "增值服务动态追加 (Upsell on the Fly)" 与 "代理商白牌化 (Agent White-Labeling)" 的具体实施步骤。根据对商户后台视图的深度分析，我们将采用**避免污染现有设置页，建立独立营销模块**的策略。

## 1. 增值服务动态追加 (Upsell on the Fly)

利用包裹入库后的"黄金时间"，由仓管员主动发起增值服务推荐，提升客单价。

### 1.1 业务流程
1.  **发现机会**: 仓管员在操作包裹时（如发现易碎品、包装破损），认为需要额外服务。
2.  **发起推荐**: 仓管员在 PDA/后台 使用 "推荐功能"，拍照并选择服务（如"气柱加固"），系统自动附带预设话术。
3.  **用户决策**: 用户收到 LINE/微信 通知（含照片），点击 "确认添加"。
4.  **挂账处理**: 服务费不立即支付，而是记录为 "待结算挂账"，在后续 "打包/出库" 结算时统一收取。

### 1.2 后端视图与配置详解 (Backend Config)

**设计原则**:
*   **多商户隔离 (Multi-tenancy)**: 所有配置数据的读写 **必须** 带上 `wxapp_id`。在 Controller 层通过 `this->store['wxapp_id']` 获取当前商户 ID，严禁跨商户读取配置。
*   **开关分级**: 采用 "总开关 -> 模块开关 -> 细节配置" 的分级策略，确保灵活可控。

#### A. 营销中心主配置 (`market/marketing/index.php`)

该页面将作为营销功能的控制台，包含三个核心标签页。数据将存储于 `yoshop_wxapp` 表的 `marketing_setting` 字段（JSON格式）或新建 `yoshop_marketing_setting` 表（推荐后者以支持索引）。

**Tab 1: 增值服务推荐 (Upsell)**

| 配置项标签 | 字段名 (Key) | 类型 | 默认 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| **功能总开关** | `upsell.enable` | Switch | Off | 关闭后，仓管端不显示推荐按钮，API 拒绝相关请求 |
| 推荐锁定时间 | `upsell.timeout` | Number | 24 | 单位小时。超时未确认则自动取消推荐 |
| 强制上传凭证 | `upsell.require_proof` | Switch | On | 开启后，仓管员必须上传照片才能提交推荐 |
| **智能触发开关** | `upsell.smart_trigger.enable` | Switch | Off | 开启后，系统根据关键词自动高亮推荐按钮 |
| 触发关键词 | `upsell.smart_trigger.keywords` | Textarea | 空 | 英文逗号分隔，如 `Monitor,Glass`。匹配预报品名 |
| 允许改价 | `upsell.allow_price_edit` | Switch | Off | 是否允许仓管员在推荐时临时调整服务价格 |

**Tab 2: 代理商白牌化 (White Label)**

| 配置项标签 | 字段名 (Key) | 类型 | 默认 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| **功能总开关** | `agent.white_label.enable` | Switch | Off | 关闭后，所有代理配置失效，统一显示平台默认 Logo |
| 最低权限门槛 | `agent.white_label.min_level` | Select | 10 | 只有 UserLevel >= 此值的代理商才有权设置白牌 |
| 强制审核 | `agent.white_label.audit` | Switch | Off | 代理商上传 Logo 后是否需要管理员审核才生效 |

**Tab 3: 分润佣金 (Commission)**

采用了 **"白名单配置模式"**，商户仅需添加需要分润的项目，未配置的项目默认不分润。

| 配置项标签 | 字段名 (Key) | 类型 | 默认 | 说明 |
| :--- | :--- | :--- | :--- | :--- |
| **功能总开关** | `commission.enable` | Switch | Off | 关闭后，不记录任何分润流水 |
| 结算方式 | `commission.settle_type` | Select | 余额 | 余额(Balance) / 积分(Points) / 线下(Offline) |
| **分润规则列表** | `commission.rules` | JSON Array | [] | 动态列表，支持增删 |
| - 规则项详情 | `{ service_id, percent }` | Object | - | 仅针对列表中存在的 service_id 计算佣金 |

---

### 1.3 数据库变更 (Schema & Multi-tenancy)

为了更好支持 `wxapp_id` 隔离，建议新建独立配置表：

*   **表名**: `yoshop_marketing_setting`
*   **结构**:
    *   `id` (int, PK)
    *   `wxapp_id` (int, index): **核心字段，物理隔离不同商户配置**
    *   `key` (varchar): 配置键，如 `upsell.enable`
    *   `value` (text): 配置值
    *   `update_time` (int)

---

## 3. 汇总：新视图文件结构

```
source/application/store/
├── controller/
│   └── market/
│       └── Marketing.php      # 必须严格校验 $this->store['wxapp_id']
└── view/
    └── market/
        └── marketing/
            └── index.php
```

### 3.1 `market/marketing/index.php` 视图实现逻辑

视图层将使用 `am-switch` (AmazeUI Switch) 来渲染开关，并利用 `name` 属性数组化提交数据。

```html
<form class="am-form" action="<?= url('market.marketing/setting') ?>" method="post">
    <!-- 隐式字段：虽然后端会从 Session 取 wxapp_id，但调试时需注意数据流向 -->
    
    <div class="tab-content">
        <!-- Tab 1: Upsell -->
        <div id="tab1">
            <!-- (代码省略，同上) -->
        </div>
        
        <!-- Tab 3: Commission -->
        <div id="tab3">
             <div class="am-form-group">
                <label>分润功能总开关</label>
                <div class="tpl-switch-btn">
                    <input type="checkbox" name="setting[commission][enable]" value="1" 
                        <?= $values['commission']['enable'] ? 'checked' : '' ?>>
                </div>
            </div>
            
            <div class="am-form-group">
                <label>分润规则配置 <small>(仅添加的项目会产生佣金)</small></label>
                <div id="commission-rules-list">
                    <!-- 动态渲染已保存的规则 -->
                    <?php if(!empty($values['commission']['rules'])): ?>
                        <?php foreach($values['commission']['rules'] as $idx => $rule): ?>
                        <div class="rule-item am-g am-margin-bottom-sm">
                            <div class="am-u-sm-5">
                                <select name="setting[commission][rules][<?= $idx ?>][service_id]" data-am-selected>
                                    <?php foreach ($all_services as $svc): ?>
                                    <option value="<?= $svc['service_id'] ?>" 
                                        <?= $svc['service_id'] == $rule['service_id'] ? 'selected' : '' ?>>
                                        <?= $svc['name'] ?> (<?= $svc['price'] ?>)
                                    </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="am-u-sm-5">
                                <div class="am-input-group am-input-group-sm">
                                    <span class="am-input-group-label">返佣比例</span>
                                    <input type="number" class="am-form-field" min="0" max="100"
                                        name="setting[commission][rules][<?= $idx ?>][percent]" 
                                        value="<?= $rule['percent'] ?>">
                                    <span class="am-input-group-label">%</span>
                                </div>
                            </div>
                            <div class="am-u-sm-2">
                                <button type="button" class="am-btn am-btn-danger am-btn-sm btn-delete-rule">
                                    <i class="am-icon-trash"></i> 删除
                                </button>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <!-- 添加按钮 -->
                <button type="button" class="am-btn am-btn-success am-btn-sm" id="btn-add-rule">
                    <i class="am-icon-plus"></i> 添加分润项目
                </button>
            </div>
        </div>
    </div>
</form>
<script>
    // 简单的 JS 逻辑用于动态添加 DOM 节点 (伪代码)
    $('#btn-add-rule').click(function() {
        var tmpl = '...'; // 获取模板
        $('#commission-rules-list').append(tmpl);
    });
</script>
```

### 1.4 Agent Notification Design (LINE Flex Message)

仓管员发起推荐后，用户将在 LINE OA 收到如下 Flex Message 卡片：

**UI 示意**:
> [图片: 包裹实拍图]
> **增值服务建议**
> 您的包裹 (TH123456) 包含易碎品，建议进行加固。
>
> **服务**: 气柱加固 (+฿20)
> **理由**: 外箱有轻微挤压
>
> [ 确认添加 ] (Primary)
> [ 忽略 ] (Secondary)

**JSON 结构 (Draft)**:
```json
{
  "type": "bubble",
  "hero": {
    "type": "image",
    "url": "https://example.com/package_photo.jpg",
    "size": "full",
    "aspectRatio": "20:13",
    "aspectMode": "cover",
    "action": { "type": "uri", "uri": "https://line.me/..." }
  },
  "body": {
    "type": "box",
    "layout": "vertical",
    "contents": [
      {
        "type": "text",
        "text": "增值服务建议",
        "weight": "bold",
        "size": "xl"
      },
      {
        "type": "text",
        "text": "您的包裹 (TH123456) 包含易碎品，建议进行加固。",
        "wrap": true,
        "color": "#666666",
        "size": "sm",
        "margin": "md"
      },
      {
        "type": "separator",
        "margin": "lg"
      },
      {
        "type": "box",
        "layout": "vertical",
        "margin": "lg",
        "spacing": "sm",
        "contents": [
          {
            "type": "box",
            "layout": "baseline",
            "spacing": "sm",
            "contents": [
              { "type": "text", "text": "服务", "color": "#aaaaaa", "size": "sm", "flex": 1 },
              { "type": "text", "text": "气柱加固 (+฿20)", "wrap": true, "color": "#666666", "size": "sm", "flex": 5 }
            ]
          },
          {
            "type": "box",
            "layout": "baseline",
            "spacing": "sm",
            "contents": [
              { "type": "text", "text": "理由", "color": "#aaaaaa", "size": "sm", "flex": 1 },
              { "type": "text", "text": "外箱有轻微挤压", "wrap": true, "color": "#666666", "size": "sm", "flex": 5 }
            ]
          }
        ]
      }
    ]
  },
  "footer": {
    "type": "box",
    "layout": "vertical",
    "spacing": "sm",
    "contents": [
      {
        "type": "button",
        "style": "primary",
        "height": "sm",
        "action": {
          "type": "postback",
          "label": "确认添加",
          "data": "action=upsell_confirm&order_id=123&service_id=5",
          "displayText": "确认添加气柱加固"
        }
      },
      {
        "type": "button",
        "style": "secondary",
        "height": "sm",
        "action": {
          "type": "postback",
          "label": "忽略",
          "data": "action=upsell_ignore&order_id=123",
          "displayText": "忽略此建议"
        }
      }
    ]
  }
}
```

### 1.5 仓管端交互设计 (Warehouse UI)

针对高频作业场景，采用 **"非阻塞式后置推荐"** (Non-blocking Post-scan Recommendation) 策略，避免打断正常的扫码入库节奏。

**改造页面**: `source/application/store/view/package/index/scan.php`

**交互流程**:
1.  **正常扫码**: 仓管员使用扫码枪进行常规入库 (`op=1`)，系统在下方表格动态插入一行包裹记录。
2.  **发现异常**: 仓管员若发现包裹外观破损或易碎，点击该行末尾新增的 **[ ⚡ 推荐服务 ]** 按钮。
3.  **弹出浮层**:
    *   **标题**: 增值服务推荐 (包裹: TH123456)
    *   **证据上传**: 调用设备摄像头或文件选择 (Web Component: `<input type="file" capture="camera">`)。
    *   **服务选择**: 下拉选择预设服务（如：气柱加固、打木架）。
    *   **理由预设**: 点击标签快速填入理由（如："[外箱破损]", "[易碎品]"）。
4.  **异步提交**: 点击确定后，浮层关闭，后台异步发送 Line 通知。表格该行状态更新为 "已推荐"。

**DOM 结构变更 (`scan.php`)**:
*   在 `renderTable` 函数中，`Actions` 列追加一个按钮：
    ```javascript
    _td += "<button class='am-btn am-btn-xs am-btn-warning' onclick='openUpsellModal(" + data.order_id + ")'>⚡ 推荐</button>";
    ```
*   新增一个隐藏的 Modal (`am-modal`) 用于承载表单。

---


### 1.6 智能触发规则 (Smart Triggers - Iteration 2)
系统根据商品预报信息与包裹属性，自动辅助仓管员发现销售机会。

*   **关键词规则**: 在包裹入库时，若预报品名命中配置的关键词（如 `Monitor`, `Glass`, `TV`），前台 UI 自动高亮 "推荐" 按钮，并提示 "易碎品 - 建议推荐打木架"。
*   **重量/尺寸规则**: 若包裹称重超过 `20kg`，自动提示 "超重 - 建议推荐分箱或托盘服务"。

### 1.7 代理商分润机制 (Agent Commission - Iteration 2)
为了激励代理商向其下级客户推广增值服务，建立分润体系。

*   **配置**: 在 `Marketing` 设置中，针对每项增值服务，可设置 "代理商返佣比例" (例如 10%)。
*   **各方获利**:
    *   **平台**: 获得主要服务费。
    *   **代理**: 获得 10% 服务费作为积分回馈。
    *   **客户**: 获得更安全的包裹保障。

---

### 1.8 技术约束 (Technical Constraints)

为了保障代码的可维护性，所有新创建或重构的文件必须遵守以下硬性规定：
*   **300行代码上限**: 任何单个文件（Controller, Model, View, JS）不得超过 300 行。
*   **模块化策略**:
    *   **控制器 (Controller)**: 业务逻辑过长时，必须下沉到 `Service` 层或提取为 `Traits`。
    *   **视图 (View)**: 必须将各 Tab 内容拆分为独立的 **局部视图 (Partials)**，主视图仅作为容器。
    *   **JavaScript**: 禁止在 PHP 视图中编写大量行内 JS，必须提取到独立的 `.js` 文件中或拆分为小函数。

---

## 2. 代理商白牌化 (Agent White-Labeling)

赋能代理商（大客户/B端用户），使其能够向其下级客户展示自有品牌形象。

### 2.1 业务流程
1.  **代理配置**: 代理商（或由管理员代操作）上传自己的 Logo、品牌色、自定义域名（可选）。
2.  **动态渲染**: 当代理商的下级客户访问物流追踪页 (H5) 或接收通知时，系统动态替换 Logo 和配色。

### 2.2 后端视图改造计划

#### A. 全局白牌化设置 (Global Switch)
*   **位置**: `source/application/store/view/market/marketing/index.php` (新模块)
*   **配置项**:
    *   `开启代理商白牌化` (Switch): 全局功能开关。
    *   `白牌化权限门槛` (Select): 允许哪些等级的用户使用白牌功能 (如: 仅限VIP代理)。

#### B. 代理商个性化设置 (Individual Config)
*   **位置**: 在 **用户详情页** 或 **代理商专属后台**。
*   **新增 Tab**: "品牌设置"
*   **字段**:
    *   `品牌名称` (Text)
    *   `品牌 Logo` (Image Upload)
    *   `主题色` (Color Picker)

### 2.3 技术实现路径
1.  **Schema**: 在 `yoshop_user` 表或新建 `yoshop_agent_config` 表中存储品牌信息。
2.  **Middleware**: 在 H5 端 API 响应中，检查当前用户的 `parent_id` (上级代理)，如果上级开启了白牌化，则返回上级的品牌配置覆盖默认配置。

---

## 3. 汇总：新视图文件结构 (Modular Structure)

为了遵循 300 行限制，我们将视图拆分为多个小文件。

```
source/application/store/
├── controller/
│   └── market/
│       └── Marketing.php      # New Controller (Keep < 300 lines)
└── view/
    └── market/
        └── marketing/
            ├── index.php      # Main Container (壳文件)
            ├── upsell.php     # Tab 1: 增值服务设置
            ├── agent.php      # Tab 2: 代理商设置
            └── commission.php # Tab 3: 分润设置
```

### 3.1 `market/marketing/index.php` 视图实现逻辑 (Container)

主视图只负责 Tabs 的骨架和 `include` 子视图，确保极简。

```html
<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <form class="am-form tpl-form-line-form" action="<?= url('market.marketing/setting') ?>" method="post">
                    <div class="widget-head">
                        <div class="widget-title am-fl">营销中心设置</div>
                        <ul class="am-tabs-nav am-cf">
                            <li class="active"><a href="#tab1">增值服务 (Upsell)</a></li>
                            <li><a href="#tab2">代理白牌 (White Label)</a></li>
                            <li><a href="#tab3">分润佣金 (Commission)</a></li>
                        </ul>
                    </div>
                    <div class="widget-body am-fr">
                        <div class="am-tabs" data-am-tabs>
                            <div class="am-tabs-bd">
                                <!-- Tab 1: Upsell -->
                                <div class="am-tab-panel am-active" id="tab1">
                                    <?php include 'upsell.php'; ?>
                                </div>
                                <!-- Tab 2: Agent -->
                                <div class="am-tab-panel" id="tab2">
                                    <?php include 'agent.php'; ?>
                                </div>
                                <!-- Tab 3: Commission -->
                                <div class="am-tab-panel" id="tab3">
                                    <?php include 'commission.php'; ?>
                                </div>
                            </div>
                        </div>
                        <div class="am-form-group">
                            <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                <button type="submit" class="j-submit am-btn am-btn-secondary">提交 / Submit</button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
```

## 4. 下一步行动
1.  **创建控制器**: `app\store\controller\market\Marketing.php`。
2.  **创建视图目录**: `app\store\view\market\marketing/`。
3.  **创建视图文件**: `index.php`, `upsell.php`, `agent.php`, `commission.php`。
4.  **修改现有视图**: 更新 `addservice/add.php` 和 `edit.php` 添加推荐字段。
