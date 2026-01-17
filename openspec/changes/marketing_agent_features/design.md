# LINE Mini App 营销与代购增强功能设计方案

## 1. 营销功能实施方案 (Marketing Implementation)

### A. 沉睡客户唤醒 (The "Wake-up" Campaign)
针对超过 30 天无互动的客户，自动发送高视觉冲击力的优惠券。

*   **触发机制**:
    *   每日定时任务 (`Task` 脚本)，扫描 `user` 表。
    *   条件: `last_login_time < (现在 - 30天)` AND `is_blocked = 0`.
*   **Flex Message 设计**:
    *   **Header**: 醒目的红色/橙色背景，标题 "We Miss You!"。
    *   **Hero**: 动态 GIF 图片（如打开礼盒的动效）。
    *   **Body**: "您有 **฿50** 运费券待领取，有效期 3 天"。
    *   **Footer**: 按钮 [立即激活优惠券] (Action: 跳转 Mini App 首页并自动弹窗领取)。
*   **技术实现**:
    *   新增 `MarketingTask.php`。
    *   记录发送日志，避免重复骚扰（每个用户每 90 天最多触发一次）。

### B. 支付后裂变 (Referral Program)
利用支付完成后的“兴奋期”，引导用户分享。

*   **触发机制**: 监听“支付成功”事件 (`PayOrder.php`)。
*   **Flex Message 设计**:
    *   **样式**: 金色/高级黑配色，模仿“黑金卡”或“礼品卡”。
    *   **文案**: "您的运费已支付！送您好友 ฿20，您也将获得 ฿20"。
    *   **交互核心**: 
        *   按钮 1 [发送给好友]: 使用 LINE URL Scheme `https://line.me/R/msg/text/?{邀请文案+带参链接}`。用户点击后直接选择好友发送。
        *   按钮 2 [分享到朋友圈]: `https://line.me/R/nv/timeline/post`。
*   **后端逻辑**: 
    *   生成带 `inviter_id` 的专属推广链接。
    *   新用户通过链接注册时，自动绑定上下级关系。

### C. 交互式服务评价 (Instant Feedback)
*   **触发机制**: 包裹状态变更为 `received` (已签收) 后 1 小时。
*   **Flex Message 设计**:
    *   **Body**: "包裹 {tracking_no} 已签收，服务如何？"
    *   **Component**: 5 个星星图标 (Icon)。
    *   **Action**: 
        *   点击星星 4-5: 触发 Postback `action=rate&score=5`，回复“感谢您的认可！”。
        *   点击星星 1-3: 跳转 Mini App 问卷页 "请告诉我们可以改进的地方"。

### D. 智能合箱建议 (Smart Consolidation Nudge)
利用算法帮用户省钱，建立信任。

*   **触发机制**: 用户尝试提交单个包裹打包时。
*   **系统检查**: 检测库存中是否还有其他 `status=arrived` 的包裹。
*   **Flex Message 拦截**:
    *   **Header**: "✋ 等一下！"
    *   **Body**: "您库存中还有 2 件包裹。如果合并打包，预计节省 ฿50。"
    *   **Action**: 
        *   [一键合并]: 跳转打包页并选中所有。
        *   [继续单件]: 继续当前流程。

---

## 2. 账号迁移与手机号隐私 (Account Migration & Privacy)

### A. LINE 官方对手机号的态度
*   **获取能力**: LINE Login API (`scope: openid email profile`) **默认不提供手机号**。虽然 LINE 有 `phonenumber` scope，但仅向通过高等级认证 (Certified Provider) 的企业开放。对于大多数 Mini App，**不能**直接通过 API "静默"获取手机号。
*   **隐私与合规**: 向用户索取手机号是合法的，前提是必须**用户主动填写**并**同意隐私协议**。

### B. "主动绑定 + SMS 验证" 方案 (推荐)
这是行业标准做法，不涉及隐私违规，因为发货本身就需要电话。

1.  **UI 流程**:
    *   用户进入 Mini App "个人中心"。
    *   提示 "绑定手机号，确保包裹通知不丢失"。
    *   用户输入手机号 -> 点击 [发送验证码] -> 输入 SMS OTP。

2.  **换号迁移逻辑 (The Migration Flow)**:
    *   **场景**: 用户换了新 LINE 账号 (New OpenID)，但手机号没变。
    *   **操作**: 用户在新号上绑定手机号 `081-234-5678`。
    *   **系统检测**: 数据库查询发现 `081-234-5678` 属于旧账号 (Old UserID: 1001)。
    *   **安全验证**: 必须通过 SMS OTP 验证，证明手机号确实在此时此刻属于该用户。
    *   **执行**: 
        *   将 Old UserID: 1001 的 `line_openid` 更新为 New OpenID。
        *   或者将 Old UserID 的数据 (Order, Address, Balance) `UPDATE user_id = New UserID` (视数据库设计而定，通常更新 OpenID 更简单)。
    *   **结果**: 用户无缝找回所有历史数据。

---

## 3. 代购 (Agent) 增强场景方案

### A. "包裹关注" (Package Subscription) - 一对多通知
解决 "一个包裹，多人接收通知" 的需求。

*   **功能逻辑**:
    1.  **开启共享**: 商户后台/代购本人开启某包裹的 "Share Connectivity" (共享连接能力)。
    2.  **生成链接**: 代购转发 Flex Message 卡片及链接 (`/package/share?id=123`) 给客户群。
    3.  **访客订阅**: 
        *   客户 A 点击链接查看包裹。
        *   页面底部显示按钮: **[🔔 订阅此包裹通知]**。
        *   客户点击后，系统在 `package_subscribers` 表记录 `{package_id: 123, openid: UserA_OpenID}`。
    4.  **广播通知**:
        *   当包裹变更为 "已发货/派送中" 时。
        *   系统遍历 `package_subscribers`，给 Owner (代购) 和所有 Subscribers (客户 A, B...) 发送 Flex Message。
*   **商户控制**: 后台设置开关 `Allow Package Subscription`。

### B. "访客链接" (Guest Link)
*   **从属文档**: 将此功能列入 API 文档 "External Access" 章节。
*   **特性**: 
    *   **Read-Only**: 仅查看物流进度，不显示运费成本、不显示代购的私人信息。
    *   **Token 验证**: 链接携带短期 Token，防止被遍历爬取。

### C. 基于 "唛头" (Mark) 的每日简报 (Daily Summary)
让代购从 "海量消息轰炸" 中解脱，提供高价值的汇总信息。

*   **痛点**: 代购每天有 50 个包裹入库，手机响 50 次，很难整理。
*   **新方案: "每日入库日报" (Daily Digest)**
*   **Flex Message 设计 (Bubble Container)**:
    *   **Header**: "📅 1月18日 入库汇总 (共 50 件)"。
    *   **Body (Box Layout - Vertical)**: 使用表格化布局展示分组数据。
        *   **行 1**: `[唛头: 客户A]` | `数量: 5 件` | `重量: 3.2kg`
        *   **行 2**: `[唛头: 客户B]` | `数量: 12 件` | `重量: 15.0kg`
        *   **行 3**: `[唛头: 客户C]` | `数量: 1 件`  | `重量: 0.5kg`
        *   *(如果超过 5 行，显示 "更多...")*
    *   **Footer**: 
        *   按钮 1: [查看完整清单] (跳转 Mini App 列表页，已按唛头过滤)。
        *   按钮 2: [一键复制单号] (点击后弹出文本，包含按唛头分组的快递单号，方便代购复制去 Excel)。
*   **触发设置**:
    *   代购可以在 "设置" 中选择: "实时通知" 或 "每日汇总模式(例如每晚 8 点发送)"。

### D. 代购品牌代入 (Agent White-Labeling)
让代购觉得这是“她们自己”的系统——SaaS 高级卖点。

*   **功能**: 
    *   代购转发给客户的 Flex Message (访客视图) 将**不显示**“小思集运”的品牌。
    *   如果是高级代购，可以动态替换为**代购自己的 Logo 和 主题色**。
*   **技术实现**:
    *   在渲染 Flex Message 时，检查 `agent_id` 的配置。
    *   动态注入 `hero.url` (Logo) 和 `header.backgroundColor` (品牌色)。

---

## 4. 总结与建议
1.  **优先开发 [访客连接] 与 [订阅功能]**: 这是代购最需要的功能，能极大减轻她们的客服压力。
2.  **合规处理手机号**: 坚持使用 SMS 验证码流程，不要试图寻找 "后门" 获取手机号，这对账号安全至关重要。

---

## 5. SaaS 商户后台配置视图 (SaaS Configuration Views)

为了满足 SaaS 模式下不同商户的个性化需求，我们需要在后台 (`store` 模块) 提供可视化的配置界面。

### A. 营销设置页面 (`setting/marketing/index`)
该页面包含三个主要选项卡 (Tabs):

#### Tab 1: 沉睡唤醒 (Wake-up Campaign)
*   **启用开关**: [ON/OFF]
*   **判定周期**: [ 输入框: 30 ] 天未登录
*   **奖励设置**:
    *   **赠送优惠券**: [下拉选择: 5元运费券 / 10元无门槛]
    *   **或赠送余额**: [ 输入框: 0.00 ] 元 (选填)
*   **推送时间**: [ 时间选择器: 10:00 ] (建议在上午)
*   **消息模板**: [下拉选择 Flex 模板] (关联 `tplMsg` 中的配置)
*   *预览区域*: 实时显示配置好的 Flex Message 样式。

#### Tab 2: 支付裂变 (Referral Program)
*   **启用开关**: [ON/OFF]
*   **奖励规则**:
    *   **发起人奖励**: [ 输入框: 20 ] 元/积分
    *   **受邀人奖励**: [ 输入框: 20 ] 元/积分
*   **分享方式配置** (多选):
    *   [x] **链接分享 (Link)**: 生成标准 `https://...` 链接
    *   [x] **二维码海报 (Poster)**: 生成带推广码的精美海报图片
    *   [ ] **卡片分享 (Flex Card)**: 生成可直接转发的 LINE 卡片
*   **海报背景图**: [上传图片/选择默认]

#### Tab 3: 代购增强 (Agent Tools)
*   **包裹分享设置**:
    *   **启用分享功能**: [ON/OFF]
    *   **访客模式 (Guest Mode)**: [ON/OFF] (允许不登录查看物流)
    *   **允许订阅 (Allow Subscribe)**: [ON/OFF] (允许访客订阅包裹通知)
    *   **分享包含信息**:
        *   [x] 物流轨迹
        *   [ ] 运费/成本 (敏感信息，默认关闭)
        *   [x] 包裹重量/尺寸
*   **每日简报 (Daily Digest)**:
    *   **启用开关**: [ON/OFF]
    *   **发送时间**: [ 时间选择器: 20:00 ]
    *   **分组依据**: [下拉选择: 唛头 (Mark) / 用户 ID]
*   **品牌代入 (White-Labeling)**:
    *   **允许代购自定义品牌**: [ON/OFF] (SaaS Pro 功能)
    *   **默认分享 Logo**: [上传图片] (当代购未设置时，显示商户默认 Logo，而非平台 Logo)

#### Tab 4: 智能策略 (Smart Logic)
*   **合箱建议 (Consolidation)**:
    *   **启用智能拦截**: [ON/OFF]
    *   **触发阈值**: 库存件数 >= [ 2 ] 件时触发提醒。


---

## 6. 数据库变更 (Schema Changes)
无需修改表结构，所有配置将存储在 `yoshop_setting` 表的 `marketing` 键值对中 (JSON 格式)，保证系统的扩展性和稳定性。

```php
// yoshop_setting `marketing` 示例结构
{
  "wakeup": { "is_enable": "1", "days": "30" ... },
  "referral": { "is_enable": "1", ... },
  "agent": { "share": { "guest_view": "1" ... } }
}
```

