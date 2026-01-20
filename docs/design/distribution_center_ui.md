# 分销中心 (Distribution Center) UI/UX 设计方案

## 1. 设计概述 (Design Overview)

本方案基于 [ui-ux-pro-max] 工作流生成，旨在为分销中心打造专业、清晰且高转化的移动端用户界面。不仅关注视觉美感，更注重信息层级和操作效率，帮助分销商快速了解收益状况并进行推广。

### 1.1 设计目标
- **信任感**: 使用专业色彩和整洁排版，建立用户对资金安全的信任。
- **激励性**: 突出显示核心收益数据（可提现佣金），激发用户推广动力。
- **高效性**: 简化提现和推广流程，减少操作步骤。

---

## 2. 设计系统 (Design System)

基于 "E-commerce Professional" 风格生成的视觉规范。

### 2.1 色彩规范 (Color Palette)

| 角色 | 色值 (Hex) | 用途 |
|------|-----------|------|
| **Primary** | `#2563EB` | 主品牌色，用于重要图标、激活状态 |
| **Secondary** | `#3B82F6` | 辅助色，用于次级按钮、背景装饰 |
| **CTA (Action)** | `#F97316` | 行动色，用于“立即提现”、“去推广”等强引导按钮 |
| **Background** | `#F8FAFC` | 页面背景色，保持干净清爽 |
| **Surface** | `#FFFFFF` | 卡片背景色 |
| **Text Primary** | `#1E293B` | 主要标题、金额数字 |
| **Text Secondary** | `#64748B` | 次要文案、说明文字 |
| **Success** | `#10B981` | 状态：审核通过、已打款 |
| **Warning** | `#F59E0B` | 状态：审核中 |

### 2.2 排版 (Typography)

- **字体家族**: Inter / Roboto (根据系统默认，优先无衬线字体)
- **层级**:
    - **Header (金额)**: 32px / Bold / #1E293B (突出核心资产)
    - **Title (页面标题)**: 18px / SemiBold / #1E293B
    - **Subtitle (卡片标题)**: 16px / Medium / #1E293B
    - **Body (正文)**: 14px / Regular / #475569
    - **Caption (辅助)**: 12px / Regular / #94A3B8

### 2.3 核心组件样式 (Component Styles)

- **卡片 (Cards)**:
    - 背景: `bg-white`
    - 圆角: `rounded-xl` (12px)
    - 阴影: `shadow-sm` (轻微阴影，保持扁平感)
    - 边框: 浅色模式下可加微弱边框 `border border-slate-100`

- **按钮 (Buttons)**:
    - **Primary**: 渐变或纯色背景，`rounded-lg`，高度 `44px` (指尖友好)。
    - **Ghost**: 无背景，仅文字颜色，用于次要操作。

---

## 3. 页面详细设计 (Page Designs)

### 3.1 分销中心首页 (Dashboard)
**路由**: `/pages/dealer/index`
**API**: `dealer.user/center`

**布局结构**:
1.  **顶部卡片 (Asset Card)**:
    -   **背景**: 使用品牌色渐变 (#2563EB -> #3B82F6)，营造高级感。
    -   **内容**:
        -   "可提现佣金" (大字号，金黄色或白色高亮)。
        -   "立即提现" 按钮 (白色 Pill Shape，文字蓝色)。
        -   次要数据行: "待结算佣金"、"已提现佣金"。
2.  **快捷功能区 (Quick Actions)**:
    -   网格布局 (Grid 2 cols)。
    -   **推广海报**: 图标 + 标题 "推广海报" + 描述 "赚取佣金"。
    -   **我的团队**: 图标 + 标题 "我的团队" + 描述 "N人"。
    -   **分销订单**: 图标 + 标题 "分销订单" + 描述 "N笔"。
    -   **提现明细**: 图标 + 标题 "提现明细".
3.  **邀请栏 (Invite Bar)**:
    -   底部固定或页面中部横幅。
    -   文案: "邀请好友，坐享收益"。
    -   按钮: "生成邀请链接"。

### 3.2 申请成为分销商 (Apply Page)
**路由**: `/pages/dealer/apply`
**API**: `dealer.apply/submit`, `dealer.user/apply`

**布局结构**:
1.  **状态横幅**:
    -   若 User 状态为“申请中”，显示全屏状态页 (Icon: 时钟, Title: "审核中", Desc: "请耐心等待...").
2.  **申请表单**:
    -   **Header**: 欢迎文案 + 背景图 (配置项 `background`).
    -   **Form**:
        -   姓名 (Input, Placeholder: "请输入真实姓名")
        -   手机号 (Input, Placeholder: "请输入手机号码")
    -   **Agreement**: 勾选框 "我已阅读并同意《分销商协议》"。
    -   **Submit Button**: 底部吸底按钮 "提交申请" (Primary Color)。

### 3.3 我的团队 (My Team)
**路由**: `/pages/dealer/team`
**API**: `dealer.team/lists`

**布局结构**:
1.  **统计头**:
    -   总人数 (大数字)。
    -   Tabs: "一级(N)", "二级(N)", "三级(N)"。
2.  **筛选栏**:
    -   Dropdown: "全部时间", "今日新增", "本周新增"。
3.  **列表项 (List Item)**:
    -   **左侧**: 用户头像 (Avatar)。
    -   **中间**: 昵称 + 加入时间 (灰色小字)。
    -   **右侧**: 贡献佣金 (绿色/品牌色数字) + 订单数。
    -   **交互**: 点击无跳转 (仅展示)，或展开看详情。

### 3.4 分销订单 (Commission Orders)
**路由**: `/pages/dealer/order`
**API**: `dealer.order/lists`

**布局结构**:
1.  **Tabs**: "全部", "已结算", "未结算"。
2.  **列表项 (Card)**:
    -   **Header**: 订单号 + 状态标签 (已结算-Green, 未结算-Gray)。
    -   **Body**: 商品缩略图/名称 (若API包含) 或 简单描述 "购买商品"。
    -   **Footer**:
        -   "实付: ￥100.00"
        -   **"佣金: +￥10.00"** (高亮，右对齐)。

### 3.5 提现申请 (Withdraw)
**路由**: `/pages/dealer/withdraw`
**API**: `dealer.withdraw/submit`, `dealer.withdraw/withdraw`

**布局结构**:
1.  **账户余额**: 显示当前可提现金额。
2.  **提现金额输入**:
    -   输入框前带 "￥" 符号。
    -   全部提现按钮 (Link Text)。
3.  **提现方式选择**:
    -   Radio List:
        -   微信 (Icon + Text)
        -   支付宝 (Icon + Text + 输入账号/姓名) -> 选中后展开输入框。
        -   银行卡 (Icon + Text + 输入卡号信息) -> 选中后展开输入框。
4.  **说明文本**: 底部灰色小字，显示最低提现额度、到账时间等。

## 4. 交互与体验 (UX & Interaction)

- **Loading Skeleton**: 数据加载时使用骨架屏，避免页面抖动。
- **Empty States**: 当列表为空时，显示插画 + 文字 "暂无数据" + 引导按钮 (如 "去推广")。
- **Toast Feedback**:
    -   复制链接成功 -> "复制成功"
    -   提交申请成功 -> "提交成功，请等待审核"
- **Error Handling**: 接口报错时，显示友好的错误提示，而非原始错误码。

## 5. 关键检查点 (Pre-Delivery Checklist)

- [ ] 所有可点击区域高度至少 **44px**。
- [ ] 价格数字统一使用专门的字体或加粗处理 (`font-bold`)。
- [ ] 状态颜色统一 (处理中=Orange, 成功=Green, 失败=Red)。
- [ ] 确保 Dark Mode 下文字对比度充足 (若支持深色模式)。
