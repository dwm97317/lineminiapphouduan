# 唛头 (UserMark) 功能对接文档

> 供前端开发参考，说明唛头功能的后端实现和 API 对接方式

## 一、功能概述

唛头 (Mark/UserMark) 是用户的专属标识码，用于：
1. 仓库识别用户包裹
2. 快速匹配包裹归属
3. 支持多唛头管理

## 二、数据结构

### 2.1 数据库表 `yoshop_user_mark`

| 字段 | 类型 | 说明 |
|------|------|------|
| `id` | int | 主键 |
| `user_id` | int | 用户ID |
| `mark` | varchar(50) | 唛头代码 |
| `markdes` | varchar(255) | 唛头描述 |
| `wxapp_id` | int | 商家ID |
| `create_time` | int | 创建时间 |

### 2.2 包裹表 `yoshop_package` 中的唛头字段

| 字段 | 类型 | 说明 |
|------|------|------|
| `usermark` | varchar(30) | 包裹关联的唛头 |

## 三、API 字段说明

### 3.1 包裹列表 API 返回的唛头字段

| API | 原始字段 | 返回字段 | 说明 |
|-----|----------|----------|------|
| `package/outside` | `usermark` | `mark` | 已重命名 |
| `package/details` | `usermark` | `usermark` | 原始字段名 |

**注意**: `package/outside` API 会将 `usermark` 重命名为 `mark` 返回

### 3.2 TypeScript 接口

```typescript
// 用户唛头
interface UserMark {
  id: number;
  user_id: number;
  mark: string;        // 唛头代码
  markdes: string;     // 唛头描述
  create_time: number;
}

// 包裹中的唛头字段
interface Package {
  // ... 其他字段
  usermark?: string;   // details API
  mark?: string;       // outside API (重命名后)
}
```

## 四、前端对接示例

### 4.1 获取唛头 (兼容两种字段名)

```javascript
const getMark = (packageInfo) => {
  return packageInfo.mark || packageInfo.usermark || '';
};
```

### 4.2 显示唛头

```jsx
// 包裹列表/详情中显示唛头
{getMark(item) && (
  <div className="flex items-center gap-2">
    <span className="text-gray-500">唛头:</span>
    <span className="font-medium text-primary-600">
      {getMark(item)}
    </span>
  </div>
)}
```

### 4.3 用户唛头列表 (User 关联)

用户模型通过 `hasMany` 关联唛头表：

```php
// User.php
public function usermark() {
    return $this->hasMany("UserMark");
}
```

前端获取用户信息时，可通过 `user.usermark` 获取用户的所有唛头列表。

## 五、业务逻辑

### 5.1 唛头用途

1. **包裹预报**: 用户预报包裹时可填写唛头
2. **仓库入库**: 仓库扫描包裹时通过唛头识别用户
3. **包裹搜索**: 支持按唛头搜索包裹

### 5.2 唛头搜索

后端支持通过唛头搜索包裹：

```php
// store/model/Package.php
->where('a.member_id|u.nickName|u.user_code|a.usermark', 'like', '%'.$search.'%')
```

### 5.3 唛头强制填写设置

系统可配置是否强制填写唛头：

```php
// adminsetting['is_force_usermark'] == 1 时必填
if ($adminsetting['is_force_usermark'] == 1 && !$data['mark']) {
    return "用户唛头为必填";
}
```

## 六、前端组件建议

### 6.1 唛头显示组件

```jsx
const MarkBadge = ({ mark }) => {
  if (!mark) return null;
  
  return (
    <span className="inline-flex items-center px-2 py-0.5 rounded-full 
                     text-xs font-medium bg-blue-100 text-blue-800">
      <svg className="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
      </svg>
      {mark}
    </span>
  );
};
```

### 6.2 在包裹卡片中使用

```jsx
// OrderCard.jsx
<InfoItem 
  label={t("package.labels.mark", "唛头")} 
  value={item.mark || item.usermark} 
/>
```

## 七、注意事项

1. **字段兼容**: 前端需同时兼容 `mark` 和 `usermark` 两种字段名
2. **空值处理**: 唛头可能为空，需做空值判断
3. **多唛头**: 一个用户可以有多个唛头，通过 `user.usermark` 数组获取
4. **搜索支持**: 包裹搜索支持按唛头模糊匹配

---

*文档版本: 1.0*
*创建日期: 2026-01-14*
