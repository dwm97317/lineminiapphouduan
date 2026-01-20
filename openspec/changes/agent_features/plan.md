# 代理商功能与增值服务动态追加实施计划

本计划旨在明确 "增值服务动态追加 (Upsell on the Fly)" 与 "代理商白牌化 (Agent White-Labeling)" 的具体实施步骤。根据对商户后台视图的深度分析，我们将采用**避免污染现有设置页，建立独立营销模块**的策略。

## 1. 增值服务动态追加 (Upsell on the Fly)

利用包裹入库后的"黄金时间"，由仓管员主动发起增值服务推荐，提升客单价。

### 1.1 业务流程
1.  **发现机会**: 仓管员在操作包裹时（如发现易碎品、包装破损），认为需要额外服务。
2.  **发起推荐**: 仓管员在 PDA/后台 使用 "推荐功能"，拍照并选择服务（如"气柱加固"），系统自动附带预设话术。
3.  **用户决策**: 用户收到 LINE/微信 通知（含照片），点击 "确认添加"。
4.  **挂账处理**: 服务费不立即支付，而是记录为 "待结算挂账"，在后续 "打包/出库" 结算时统一收取。

### 1.2 后端视图改造计划

### 1.2 Backend View Modifications

#### A. Modify Package Services (Configuration)
Existing files: `source/application/store/view/setting/package/add.php` and `edit.php`
This view manages generic packing services (e.g., Wooden Crate, Waterproof Bag).
- **Add Fields**:
    - `Allow Recommend` (Switch): Only visible to warehouse staff when enabled.
    - `Default Recommend Reason` (Textarea): Pre-set marketing text.
- **Goal**: Fine-grained control over service recommendation.

#### B. New Marketing Settings Module (Settings)
现有文件 `source/application/store/view/setting/store.php` 过于拥挤，不适合添加新功能。
*   **新建视图**: `source/application/store/view/market/upsell/setting.php` (或集成在 `marketing/index.php`)
*   **配置项**:
    *   `推荐锁定时间` (Number): 推荐发出后，包裹锁定 N 小时，等待用户确认。超时自动释放。
    *   `是否强制图证` (Switch): 仓管员发起推荐时，是否强制要求上传照片作为证据。

### 1.3 数据库变更
*   表 `yoshop_order_service` (如果存在) 或 `yoshop_service`:
    *   新增 `is_recommend` (tinyint): 是否允许推荐。
    *   新增 `recommend_reason` (varchar): 默认推荐文案。

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

## 3. 汇总：新视图文件结构

为了保持代码整洁，我们将创建一个新的 `Marketing` 控制器和视图目录。

```
source/application/store/
├── controller/
│   └── market/
│       └── Marketing.php      # 新控制器：处理营销与代理设置
└── view/
    └── market/
        └── marketing/
            └── index.php      # 新视图：包含 "增值服务推荐设置" 和 "代理商功能设置" 两个Tab
```

### 3.1 `market/marketing/index.php` 视图结构概览
```html
<div class="widget-head">
    <ul class="am-tabs-nav">
        <li class="active"><a href="#tab1">增值服务推荐设置 (Upsell)</a></li>
        <li><a href="#tab2">代理商功能设置 (Agent)</a></li>
    </ul>
</div>
<div class="tab-content">
    <!-- Tab 1: Upsell -->
    <div id="tab1">
        <input name="upsell[timeout]" label="推荐锁定超时(小时)">
        <input name="upsell[require_proof]" type="checkbox" label="强制上传凭证">
    </div>
    <!-- Tab 2: Agent -->
    <div id="tab2">
        <input name="agent[white_label_enable]" type="checkbox" label="开启白牌化功能">
        <select name="agent[min_level]" label="最低可用等级">
    </div>
</div>
```

## 4. 下一步行动
1.  **创建控制器**: `app\store\controller\market\Marketing.php`。
2.  **创建视图**: `app\store\view\market\marketing\index.php`。
3.  **修改现有视图**: 更新 `addservice/add.php` 和 `edit.php` 添加推荐字段。
