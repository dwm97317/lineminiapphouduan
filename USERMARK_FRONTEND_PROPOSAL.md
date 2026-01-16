# 唛头前端功能方案

> 基于现有后端 API，实现唛头展示功能

## 一、核心逻辑

| 状态 | 处理方式 |
|------|----------|
| 无唛头 | 保持现状，使用 UID |
| 有唛头 | 显示唛头功能入口 |

## 二、数据来源

| 数据 | API | 字段 |
|------|-----|------|
| 用户唛头列表 | `user/detail` | `userInfo.usermark[]` |
| 包裹唛头 | `package/outside` | `mark` |
| 包裹唛头 | `package/details` | `usermark` |

---

## 三、功能实现

### 3.1 个人中心 - 唛头入口

**条件显示**：只有当 `usermark.length > 0` 时才显示

```
┌─────────────────────────────────────────────┐
│  👤 个人中心                                 │
├─────────────────────────────────────────────┤
│                                             │
│  UID: 10086                                 │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │  🏷️ 我的唛头                    →   │   │  ← 点击进入唛头详情
│  └─────────────────────────────────────┘   │
│                                             │
│  其他功能...                                 │
│                                             │
└─────────────────────────────────────────────┘
```

```jsx
// pages/Mine/Index.jsx
const MarkEntry = ({ userInfo }) => {
  const marks = userInfo?.usermark || [];
  const navigate = useNavigate();
  
  if (marks.length === 0) return null;
  
  return (
    <div 
      onClick={() => navigate('/mark')}
      className="bg-white rounded-2xl p-4 shadow-sm flex items-center justify-between
                 active:scale-[0.98] transition-transform cursor-pointer"
    >
      <div className="flex items-center gap-3">
        <span className="text-xl">🏷️</span>
        <span className="font-medium text-gray-800">我的唛头</span>
      </div>
      <span className="text-gray-400">→</span>
    </div>
  );
};
```

### 3.2 唛头详情页

```
┌─────────────────────────────────────────────┐
│  ← 我的唛头                                  │
├─────────────────────────────────────────────┤
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │  ABC123              [复制]          │   │
│  │  淘宝专用                            │   │
│  └─────────────────────────────────────┘   │
│                                             │
│  ┌─────────────────────────────────────┐   │
│  │  XYZ789              [复制]          │   │
│  │  拼多多专用                          │   │
│  └─────────────────────────────────────┘   │
│                                             │
└─────────────────────────────────────────────┘
```

```jsx
// pages/Mark/Index.jsx
import { useRecoilValue } from 'recoil';
import { userInfoState } from '@/store/user';
import { toast } from 'react-hot-toast';

const MarkPage = () => {
  const userInfo = useRecoilValue(userInfoState);
  const marks = userInfo?.usermark || [];
  
  const handleCopy = async (text) => {
    await navigator.clipboard.writeText(text);
    toast.success('已复制');
  };

  return (
    <div className="min-h-screen bg-gray-50 p-4">
      <div className="space-y-3">
        {marks.map((item, index) => (
          <div key={item.id || index} 
               className="bg-white rounded-2xl p-4 shadow-sm">
            <div className="flex items-center justify-between">
              <div>
                <div className="font-bold text-gray-800 text-lg">{item.mark}</div>
                {item.markdes && (
                  <div className="text-sm text-gray-500 mt-1">{item.markdes}</div>
                )}
              </div>
              <button
                onClick={() => handleCopy(item.mark)}
                className="px-4 py-2 bg-primary-500 text-white text-sm rounded-xl
                           active:scale-95 transition-transform"
              >
                复制
              </button>
            </div>
          </div>
        ))}
      </div>
    </div>
  );
};

export default MarkPage;
```

### 3.3 仓库地址页 - 唛头选择

**条件显示**：有唛头时显示唛头选择器，无唛头时保持现状（使用 UID）

```jsx
// pages/Warehouse/Address.jsx
const WarehouseAddressCard = ({ warehouse, userInfo }) => {
  const marks = userInfo?.usermark || [];
  const hasMarks = marks.length > 0;
  
  // 有唛头时使用唛头，无唛头时使用 UID
  const [selectedId, setSelectedId] = useState(() => {
    if (!hasMarks) return String(userInfo?.uid || userInfo?.id || '');
    return marks[0]?.mark || '';
  });

  const handleCopy = async () => {
    const receiverName = `${selectedId} ${warehouse.contact}`;
    const text = `收件人: ${receiverName}\n地址: ${warehouse.address}\n电话: ${warehouse.phone}`;
    await navigator.clipboard.writeText(text);
    toast.success('已复制');
  };

  return (
    <div className="bg-white rounded-2xl shadow-sm p-4">
      <div className="font-semibold text-gray-800 mb-3">{warehouse.name}</div>
      
      {/* 有唛头时显示选择器 */}
      {hasMarks && marks.length > 1 && (
        <div className="mb-3">
          <div className="text-sm text-gray-500 mb-2">选择唛头:</div>
          <div className="flex flex-wrap gap-2">
            {marks.map((item) => (
              <button
                key={item.id || item.mark}
                onClick={() => setSelectedId(item.mark)}
                className={`px-3 py-1.5 rounded-lg text-sm transition-all
                  ${selectedId === item.mark
                    ? 'bg-primary-500 text-white'
                    : 'bg-gray-100 text-gray-600'
                  }`}
              >
                {item.mark}
              </button>
            ))}
          </div>
        </div>
      )}

      {/* 地址信息 */}
      <div className="space-y-2 text-sm text-gray-600">
        <div>收件人: <span className="text-gray-800 font-medium">{selectedId}</span> {warehouse.contact}</div>
        <div>地址: {warehouse.address}</div>
        <div>电话: {warehouse.phone}</div>
      </div>

      <button
        onClick={handleCopy}
        className="w-full mt-4 py-2.5 bg-primary-500 text-white rounded-xl
                   active:scale-[0.98] transition-transform"
      >
        复制地址
      </button>
    </div>
  );
};
```

### 3.4 包裹列表/详情 - 唛头显示

**条件显示**：包裹有唛头字段时显示

```jsx
// 包裹列表 - OrderCard.jsx
{(item.mark || item.usermark) && (
  <span className="px-2 py-0.5 bg-gray-100 text-gray-600 text-xs rounded">
    {item.mark || item.usermark}
  </span>
)}

// 包裹详情 - Detail.jsx
{(pack_info.mark || pack_info.usermark) && (
  <div className="bg-white rounded-2xl p-4 shadow-sm">
    <div className="text-sm text-gray-500">唛头</div>
    <div className="font-bold text-gray-800 text-lg mt-1">
      {pack_info.mark || pack_info.usermark}
    </div>
  </div>
)}
```

---

## 四、路由配置

```jsx
// router/index.jsx
{
  path: '/mark',
  element: <MarkPage />,
  meta: { title: '我的唛头' }
}
```

---

## 五、实施清单

| 文件 | 修改内容 |
|------|----------|
| `pages/Mark/Index.jsx` | 新增唛头详情页 |
| `pages/Mine/Index.jsx` | 添加唛头入口（条件显示） |
| `pages/Warehouse/Address.jsx` | 唛头选择器（条件显示） |
| `components/Order/OrderCard.jsx` | 包裹唛头显示 |
| `pages/Order/Detail.jsx` | 包裹详情唛头显示 |
| `router/index.jsx` | 添加 /mark 路由 |

---

## 六、工时估算

| 任务 | 工时 |
|------|------|
| 唛头详情页 | 1h |
| 个人中心入口 | 0.5h |
| 仓库地址唛头选择 | 1h |
| 包裹唛头显示 | 0.5h |
| 测试 | 1h |

**总计**: 约 4 小时

---

*文档版本: 5.0*
*更新日期: 2026-01-14*
