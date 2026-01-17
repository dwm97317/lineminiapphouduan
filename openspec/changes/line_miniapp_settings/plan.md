# Line 小程序与商户后台配置集成计划 (全面升级版)

## 1. 目标
不仅实现 `LINE` 专属配置的对齐，更要将商户后台中通用的 **用户端配置 (User Client Settings)** 和 **店铺设置 (Store Settings)** 全面接入 Line 小程序，确保小程序的前端表现（表单字段、验证规则、引导图等）完全受控于后台配置。

## 2. 后台配置清单 (全面梳理)

### A. LINE 专属配置 (Line Config)
*源文件: `store/view/setting/line_config/index.php`*
*   **登录与Bot**: 开关、LIFF ID、Bot 关注策略。
*   **消息通知**: 模版消息内容与开关。
*   **LINE Pay**: 支付开关与凭证。
*   **客户联系**: 泰国热线、LINE ID、WeChat ID (专属)。

### B. 用户端功能配置 (User Client Settings)
*源文件: `store/view/setting/userclient.php`*
此部分对 Line 小程序的用户体验至关重要，决定了表单的复杂度。
*   **预报功能 (Parcel Forecast)**: 
    *   开关: `yubao.is_single` (单票), `yubao.is_more` (批量)。
    *   **字段显隐控制**: `is_country` (国家), `is_shop` (仓库), `is_expressname` (快递公司), `is_category` (品类), `is_price` (价值), `is_remark` (备注), `is_images` (图片), `is_goodslist` (物品列表)。
    *   **强制校验**: 上述每个字段对应的 `_force` 开关。
    *   **协议**: `is_xieyi` (显示协议), `is_xieyi_force` (强制勾选)。
*   **用户资料 (User Profile)**:
    *   **字段控制**: `is_identification_card` (身份证), `is_birthday` (生日), `is_wechat` (微信号), `is_email` (邮箱), `is_mobile` (手机号)。
    *   **图片**: 身份证照片上传开关。
*   **打包与配送 (Packaging & Delivery)**:
    *   `packit.is_force`: 提交打包前是否强制完善资料。
    *   `packit.is_packagestation`: 是否显示自提点选项。
    *   `packit.is_todoor`: 是否显示送货上门选项。
    *   `packit.is_waitreceivedmoney`: 是否允许填写代收货款。
*   **引导与注册**:
    *   Home Guide: 首页引导图及跳转链接。
    *   Login: `is_phone` (是否显示手机号前缀选择)。

### C. 店铺通用设置 (Store Settings)
*源文件: `store/view/setting/store.php`*
*   **基础信息**: 商户名称、分享标题/图片。
*   **单位设置**: 重量单位 (kg/lb), 长度单位 (cm/in), 货币单位 (¥/$).
*   **通用客服**: 电话、微信 (若 Line 专属配置为空，可回退使用此配置)。
*   **地址复制**: 仓库地址的显示格式 (`link_mode`, `address_mode`).

---

## 3. 差距分析与 API 需求

### 3.1. 缺失的 API 字段
当前 `LineApp::base()` 仅返回了极少量的配置。为了实现上述动态功能，我们需要大幅扩展 API 返回的数据结构。

**行动项**: 修改 `source/application/api/controller/LineApp.php` 中的 `base()` 方法 (或新增 `config()` 方法)。

**建议 API 响应结构**:
```json
{
    "code": 1,
    "msg": "success",
    "data": {
        "line": { ... },       // 现有的 Line Config
        "store": {             // 店铺基础信息
             "name": "...",
             "units": { "weight": "kg", "currency": "THB" },
             "contact": { ... } // 合并后的联系方式
        },
        "client": {            // User Client Settings - 关键新增
             "forecast": {     // 对应 yubao.*
                 "show_country": 1, "force_country": 0,
                 "show_images": 1, "force_images": 1,
                 ...
             },
             "profile": {      // 对应 userinfo.*
                 "show_id_card": 1, ...
             },
             "packaging": {    // 对应 packit.*
                 "allow_self_pickup": 1, ...
             },
             "guide": { ... }  // 引导图数据
        }
    }
}
```

---

## 4. 前端视图实施计划

### 4.1. 预报页面 (`pages/package/report` 或 `forecast`)
*   **动态表单**: 页面加载时获取 `client.forecast` 配置。
*   **逻辑**:
    *   如果 `show_images == 0`，隐藏上传图片组件。
    *   如果 `force_category == 1`，提交时校验品类必填。
    *   如果 `is_xieyi == 1`，底部渲染 "我已阅读并同意..." 勾选框。

### 4.2. 个人资料页面 (`pages/user/profile`)
*   **动态渲染**: 根据 `client.profile` 配置渲染输入框。
*   **验证**: 根据配置决定是否校验身份证格式、生日等。

### 4.3. 提交打包页面 (`pages/package/pack`)
*   **配送方式**:
    *   如果 `client.packaging.allow_self_pickup == 0`，隐藏 "自提" Tab。
    *   如果 `client.packaging.allow_todoor == 0`，隐藏 "送货" Tab。
*   **前置校验**: 如果 `packit.is_force == 1` 且资料不完整，弹窗提示跳转资料页。

### 4.4. 首页 (`pages/home/index`)
*   **引导图**: 使用 `client.guide` 数据渲染轮播图或广告位。

## 5. 总结
本计划将 Line 小程序从一https://github.com/win4r/agent-skills-code-review-router.git个 "静态表单" 应用升级为 "完全可配置" 的 SaaS 客户端。商户在后台的每一个开关操作，都能实时反映在小程序前端，极大地降低了定制化开发的成本。
