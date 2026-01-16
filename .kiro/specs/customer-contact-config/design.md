# Design Document - 客户联系配置功能

## Overview

本功能为 LINE Mini App 系统添加客户联系信息配置能力，包括后台配置界面和前端展示组件。采用现有的 Setting 模型存储数据，通过 API 接口提供给前端使用。

## Architecture

```
┌─────────────────────────────────────────────────────────────┐
│                     商户后台管理系统                          │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  LINE 设置页面 (line_config/index.php)               │   │
│  │  ┌────────────────────────────────────────────────┐  │   │
│  │  │  客户联系配置区域 (Customer Contact Section)    │  │   │
│  │  │  - Hotline (TH) Input                         │  │   │
│  │  │  - LINE Support Input                         │  │   │
│  │  │  - WeChat Input                               │  │   │
│  │  │  - Save Button                                │  │   │
│  │  └────────────────────────────────────────────────┘  │   │
│  └──────────────────────────────────────────────────────┘   │
│                           ↓                                  │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  LineConfig Controller                               │   │
│  │  - save() method                                     │   │
│  │  - Validation                                        │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     数据库层 (Database)                      │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  yoshop_setting 表                                   │   │
│  │  key: 'customer_contact'                            │   │
│  │  values: {                                          │   │
│  │    "hotline_th": "xxx",                            │   │
│  │    "line_support": "xxx",                          │   │
│  │    "wechat": "xxx"                                 │   │
│  │  }                                                  │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     API 接口层 (API)                         │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Page Controller                                     │   │
│  │  - customerContact() method                          │   │
│  │  - GET /api/page/customer_contact                    │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
                            ↓
┌─────────────────────────────────────────────────────────────┐
│                     前端应用 (Frontend)                      │
│  ┌──────────────────────────────────────────────────────┐   │
│  │  Home Page (Index.jsx)                               │   │
│  │  ┌────────────────────────────────────────────────┐  │   │
│  │  │  CustomerContact Component                     │  │   │
│  │  │  - Hotline Button (tel: link)                 │  │   │
│  │  │  - LINE Button (line.me link)                 │  │   │
│  │  │  - WeChat Display (copy to clipboard)        │  │   │
│  │  └────────────────────────────────────────────────┘  │   │
│  └──────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

## Components and Interfaces

### 1. 后端组件

#### 1.1 LineConfig Controller 扩展
**文件**: `Lineminiapp/source/application/store/controller/setting/LineConfig.php`

**新增方法**: 在现有 `save()` 方法中添加客户联系配置的处理

```php
// 在 save() 方法中添加
$customerContact = [
    'hotline_th' => $this->request->post('hotline_th', ''),
    'line_support' => $this->request->post('line_support', ''),
    'wechat' => $this->request->post('wechat', '')
];

// 验证数据
$this->validateCustomerContact($customerContact);

// 保存到 Setting 模型
SettingModel::edit('customer_contact', $customerContact, $wxapp_id);
```

**验证方法**:
```php
private function validateCustomerContact($data) {
    // Hotline: 允许数字、+、-、空格、括号
    if (!empty($data['hotline_th']) && !preg_match('/^[\d\s\+\-\(\)]+$/', $data['hotline_th'])) {
        return $this->renderError('电话号码格式不正确');
    }
    
    // LINE Support: 允许字母、数字、下划线、点
    if (!empty($data['line_support']) && !preg_match('/^[a-zA-Z0-9_\.]+$/', $data['line_support'])) {
        return $this->renderError('LINE ID 格式不正确');
    }
    
    // WeChat: 允许字母、数字、下划线、连字符
    if (!empty($data['wechat']) && !preg_match('/^[a-zA-Z0-9_\-]+$/', $data['wechat'])) {
        return $this->renderError('微信号格式不正确');
    }
    
    return true;
}
```

#### 1.2 Page Controller 扩展
**文件**: `Lineminiapp/source/application/api/controller/Page.php`

**新增方法**:
```php
/**
 * 获取客户联系配置
 * @return array
 */
public function customerContact() {
    $wxapp_id = $this->request->param('wxapp_id', 10001);
    $config = SettingModel::getItem('customer_contact', $wxapp_id);
    
    // 如果配置不存在，返回空对象
    if (empty($config)) {
        $config = [
            'hotline_th' => '',
            'line_support' => '',
            'wechat' => ''
        ];
    }
    
    return $this->renderSuccess($config);
}

// 驼峰命名别名（ThinkPHP 路由兼容）
public function CustomerContact() {
    return $this->customerContact();
}
```

### 2. 前端组件

#### 2.1 CustomerContact 组件
**文件**: `zalo_mini_app-master/src/components/CustomerContact/Index.jsx`

```jsx
import React from 'react';
import { useTranslation } from 'react-i18next';

const CustomerContact = ({ config }) => {
  const { t } = useTranslation();

  // 如果所有联系方式都为空，不显示组件
  if (!config?.hotline_th && !config?.line_support && !config?.wechat) {
    return null;
  }

  const handleCopyWeChat = () => {
    navigator.clipboard.writeText(config.wechat);
    // 显示复制成功提示
  };

  return (
    <div className="bg-white rounded-2xl p-4 shadow-sm mb-4">
      <h3 className="text-lg font-bold text-gray-800 mb-3 flex items-center gap-2">
        <svg className="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 20 20">
          <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
        </svg>
        {t('customer_contact.title', 'ฝ่ายบริการลูกค้า')}
      </h3>
      
      <div className="space-y-2">
        {/* Hotline */}
        {config.hotline_th && (
          <a
            href={`tel:${config.hotline_th}`}
            className="flex items-center gap-3 p-3 bg-gradient-to-r from-blue-50 to-blue-100 rounded-xl hover:shadow-md transition-all"
          >
            <div className="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center">
              <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                <path d="M2 3a1 1 0 011-1h2.153a1 1 0 01.986.836l.74 4.435a1 1 0 01-.54 1.06l-1.548.773a11.037 11.037 0 006.105 6.105l.774-1.548a1 1 0 011.059-.54l4.435.74a1 1 0 01.836.986V17a1 1 0 01-1 1h-2C7.82 18 2 12.18 2 5V3z" />
              </svg>
            </div>
            <div className="flex-1">
              <div className="text-xs text-gray-500">{t('customer_contact.hotline', 'Hotline (TH)')}</div>
              <div className="font-semibold text-blue-600">{config.hotline_th}</div>
            </div>
            <svg className="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        )}

        {/* LINE Support */}
        {config.line_support && (
          <a
            href={`https://line.me/ti/p/~${config.line_support}`}
            target="_blank"
            rel="noopener noreferrer"
            className="flex items-center gap-3 p-3 bg-gradient-to-r from-green-50 to-green-100 rounded-xl hover:shadow-md transition-all"
          >
            <div className="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
              <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.365 9.863c.349 0 .63.285.63.631 0 .345-.281.63-.63.63H17.61v1.125h1.755c.349 0 .63.283.63.63 0 .344-.281.629-.63.629h-2.386c-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63h2.386c.346 0 .627.285.627.63 0 .349-.281.63-.63.63H17.61v1.125h1.755zm-3.855 3.016c0 .27-.174.51-.432.596-.064.021-.133.031-.199.031-.211 0-.391-.09-.51-.25l-2.443-3.317v2.94c0 .344-.279.629-.631.629-.346 0-.626-.285-.626-.629V8.108c0-.27.173-.51.43-.595.06-.023.136-.033.194-.033.195 0 .375.104.495.254l2.462 3.33V8.108c0-.345.282-.63.63-.63.345 0 .63.285.63.63v4.771zm-5.741 0c0 .344-.282.629-.631.629-.345 0-.627-.285-.627-.629V8.108c0-.345.282-.63.63-.63.346 0 .628.285.628.63v4.771zm-2.466.629H4.917c-.345 0-.63-.285-.63-.629V8.108c0-.345.285-.63.63-.63.348 0 .63.285.63.63v4.141h1.756c.348 0 .629.283.629.63 0 .344-.282.629-.629.629M24 10.314C24 4.943 18.615.572 12 .572S0 4.943 0 10.314c0 4.811 4.27 8.842 10.035 9.608.391.082.923.258 1.058.59.12.301.079.766.038 1.08l-.164 1.02c-.045.301-.24 1.186 1.049.645 1.291-.539 6.916-4.078 9.436-6.975C23.176 14.393 24 12.458 24 10.314" />
              </svg>
            </div>
            <div className="flex-1">
              <div className="text-xs text-gray-500">{t('customer_contact.line', 'LINE Support')}</div>
              <div className="font-semibold text-green-600">@{config.line_support}</div>
            </div>
            <svg className="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
            </svg>
          </a>
        )}

        {/* WeChat */}
        {config.wechat && (
          <div
            onClick={handleCopyWeChat}
            className="flex items-center gap-3 p-3 bg-gradient-to-r from-emerald-50 to-emerald-100 rounded-xl hover:shadow-md transition-all cursor-pointer"
          >
            <div className="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center">
              <svg className="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                <path d="M8.691 2.188C3.891 2.188 0 5.476 0 9.53c0 2.212 1.17 4.203 3.002 5.55a.59.59 0 01.213.665l-.39 1.48c-.019.07-.048.141-.048.213 0 .163.13.295.29.295a.326.326 0 00.167-.054l1.903-1.114a.864.864 0 01.717-.098 10.16 10.16 0 002.837.403c.276 0 .543-.027.811-.05-.857-2.578.157-4.972 1.932-6.446 1.703-1.415 3.882-1.98 5.853-1.838-.576-3.583-4.196-6.348-8.596-6.348zM5.785 5.991c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178A1.17 1.17 0 014.623 7.17c0-.651.52-1.18 1.162-1.18zm5.813 0c.642 0 1.162.529 1.162 1.18a1.17 1.17 0 01-1.162 1.178 1.17 1.17 0 01-1.162-1.178c0-.651.52-1.18 1.162-1.18zm5.34 2.867c-1.797-.052-3.746.512-5.28 1.786-1.72 1.428-2.687 3.72-1.78 6.22.942 2.453 3.666 4.229 6.884 4.229.826 0 1.622-.12 2.361-.336a.722.722 0 01.598.082l1.584.926a.272.272 0 00.14.047c.134 0 .24-.111.24-.247 0-.06-.023-.12-.038-.177l-.327-1.233a.582.582 0 01.265-.694c1.584-1.168 2.545-2.894 2.545-4.75 0-3.55-3.534-6.436-7.892-6.853zm-2.53 3.274c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.969-.982zm4.844 0c.535 0 .969.44.969.982a.976.976 0 01-.969.983.976.976 0 01-.969-.983c0-.542.434-.982.969-.982z" />
              </svg>
            </div>
            <div className="flex-1">
              <div className="text-xs text-gray-500">{t('customer_contact.wechat', 'WeChat')}</div>
              <div className="font-semibold text-emerald-600">{config.wechat}</div>
            </div>
            <svg className="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </div>
        )}
      </div>
    </div>
  );
};

export default CustomerContact;
```

#### 2.2 Home Page 集成
**文件**: `zalo_mini_app-master/src/pages/Home/Index.jsx`

在现有的数据获取逻辑中添加：
```jsx
const [customerContact, setCustomerContact] = useState({});

useEffect(() => {
  const fetchData = async () => {
    try {
      const [lineRes, bannerRes, commentRes, contactRes] = await Promise.all([
        request.get("page/goods_line&wxapp_id=10001"),
        request.get("page/banner&wxapp_id=10001"),
        request.get("comment/hotComment&wxapp_id=10001"),
        request.get("page/customer_contact&wxapp_id=10001") // 新增
      ]);
      
      setLines(lineRes.data || []);
      setBanners(bannerRes.data || []);
      setComments(commentRes.data || []);
      setCustomerContact(contactRes.data || {}); // 新增
    } catch (error) {
      console.error("Home data fetch error:", error);
    }
  };
  
  fetchData();
}, []);

// 在 JSX 中添加
<CustomerContact config={customerContact} />
```

### 3. 后台视图扩展

#### 3.1 LINE 配置页面
**文件**: `Lineminiapp/source/application/store/view/setting/line_config/index.php`

在现有表单中添加客户联系配置区域：

```php
<!-- 客户联系配置 -->
<div class="form-group">
    <label class="col-sm-2 control-label">
        <span class="text-danger">*</span>
        ฝ่ายบริการลูกค้า (客户联系)
    </label>
    <div class="col-sm-10">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h4 class="panel-title">客户服务联系方式配置</h4>
            </div>
            <div class="panel-body">
                <!-- Hotline (TH) -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">Hotline (TH)</label>
                    <div class="col-sm-9">
                        <input type="text" 
                               class="form-control" 
                               name="hotline_th" 
                               value="<?= isset($values['customer_contact']['hotline_th']) ? $values['customer_contact']['hotline_th'] : '' ?>"
                               placeholder="例如: +66 2 123 4567">
                        <span class="help-block">泰国客服热线电话</span>
                    </div>
                </div>
                
                <!-- LINE Support -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">LINE Support</label>
                    <div class="col-sm-9">
                        <input type="text" 
                               class="form-control" 
                               name="line_support" 
                               value="<?= isset($values['customer_contact']['line_support']) ? $values['customer_contact']['line_support'] : '' ?>"
                               placeholder="例如: yourlineid">
                        <span class="help-block">LINE 官方账号 ID（不含 @）</span>
                    </div>
                </div>
                
                <!-- WeChat -->
                <div class="form-group">
                    <label class="col-sm-3 control-label">WeChat</label>
                    <div class="col-sm-9">
                        <input type="text" 
                               class="form-control" 
                               name="wechat" 
                               value="<?= isset($values['customer_contact']['wechat']) ? $values['customer_contact']['wechat'] : '' ?>"
                               placeholder="例如: yourwechatid">
                        <span class="help-block">微信客服账号</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
```

## Data Models

### Setting 模型数据结构

```php
[
    'key' => 'customer_contact',
    'values' => [
        'hotline_th' => '+66 2 123 4567',
        'line_support' => 'yourlineid',
        'wechat' => 'yourwechatid'
    ],
    'wxapp_id' => 10001,
    'store_id' => 10001
]
```

## Error Handling

### 后端错误处理

1. **验证错误**: 返回具体的错误信息
2. **数据库错误**: 记录日志并返回通用错误信息
3. **权限错误**: 返回 403 状态码

### 前端错误处理

1. **API 调用失败**: 显示错误提示，不影响其他功能
2. **数据为空**: 不显示客户联系组件
3. **复制失败**: 显示复制失败提示

## Testing Strategy

### 单元测试

1. 测试数据验证逻辑
2. 测试 API 接口返回格式
3. 测试组件渲染逻辑

### 集成测试

1. 测试后台保存流程
2. 测试前端获取流程
3. 测试链接跳转功能

### 用户验收测试

1. 商户可以成功配置客户联系信息
2. 前端可以正确显示客户联系信息
3. 所有链接可以正常工作
4. 界面美观、响应式

## Performance Considerations

1. **缓存策略**: 前端缓存客户联系配置，减少 API 调用
2. **懒加载**: 组件按需加载
3. **数据库索引**: 在 `yoshop_setting` 表的 `key` 字段上建立索引

## Security Considerations

1. **输入验证**: 严格验证所有输入数据
2. **XSS 防护**: 对输出数据进行转义
3. **权限控制**: 只有管理员可以修改配置
4. **日志记录**: 记录所有配置修改操作

## Internationalization

### 翻译键值

```javascript
{
  "customer_contact": {
    "title": "ฝ่ายบริการลูกค้า",
    "hotline": "Hotline (TH)",
    "line": "LINE Support",
    "wechat": "WeChat",
    "copy_success": "คัดลอกสำเร็จ",
    "copy_failed": "คัดลอกล้มเหลว"
  }
}
```

## Deployment Notes

1. 更新数据库（如需要）
2. 部署后端代码
3. 部署前端代码
4. 清除缓存
5. 测试功能
6. 通知商户新功能上线
