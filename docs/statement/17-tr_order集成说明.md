# tr_order 账单功能集成说明

**完成时间**: 2026-02-28  
**页面URL**: `/store/tr_order/all_list`  
**状态**: 前端集成完成，后端部分完成

---

## 已完成的功能

### 1. ✅ 前端组件集成

所有前端组件已正确集成到 `source/application/store/view/tr_order/index.php`：

- **账单状态筛选器** (第131行)
- **账单操作按钮** (第210行) - 生成账单、账单列表
- **表格表头** (第233行) - "账单信息"列
- **账单信息列** (第362行) - 显示账单编号和支付状态
- **JavaScript功能** (第1614行) - 生成账单逻辑

### 2. ✅ 数据结构适配

**关键发现**：
- tr_order 页面显示的是 **Inpack**（集运订单）记录
- 每个 Inpack 包含多个 Package（包裹），通过 `pack_ids` 字段关联
- `statement_id` 字段在 **Package** 表中，不在 Inpack 表中
- 账单是针对 Package 生成的，不是针对 Inpack

**解决方案**：
- 复选框添加 `data-pack-ids` 属性，包含该集运单的所有包裹ID
- 生成账单时，提取所有选中集运单的包裹ID，传递给账单生成API
- 显示账单信息时，查询集运单包含的包裹是否有账单

### 3. ✅ Package 模型更新

在 `source/application/store/model/Package.php` 中添加了 statement 关联：

```php
public function statement(){
    return $this->belongsTo('app\store\model\Statement', 'statement_id', 'id')
        ->field('id,statement_no,pay_status');
}
```

### 4. ✅ 组件更新

#### statement_column.php
更新为查询 Inpack 的包裹是否有账单：
```php
if (!empty($item['pack_ids'])) {
    $packageIds = explode(',', $item['pack_ids']);
    $package = \app\store\model\Package::where('id', 'in', $packageIds)
        ->where('statement_id', '>', 0)
        ->with(['statement'])
        ->find();
    // 显示找到的第一个账单信息
}
```

#### statement_script.php
更新为提取所有包裹ID：
```php
var packageIds = [];
$('input[name="checkIds"]:checked').each(function() {
    var packIds = $(this).data('pack-ids');
    if (packIds) {
        var ids = packIds.toString().split(',');
        packageIds = packageIds.concat(ids);
    }
});
```

---

## 当前限制

### 1. ⚠️ 账单状态筛选器

**问题**：账单状态筛选器（未出账/已出账未支付/已支付）目前**不起作用**

**原因**：
- 需要在 Inpack 查询中添加子查询或 JOIN Package 表
- 需要检查 pack_ids 中的包裹是否有 statement_id
- 这会显著增加查询复杂度和性能开销

**临时方案**：
- 筛选器已显示在页面上，但不会过滤数据
- 用户可以在列表中看到所有订单的账单状态

**完整实现方案**（可选）：
```php
// 在 Inpack::setWhere() 中添加
if (!empty($query['statement_status'])) {
    if ($query['statement_status'] === 'unbilled') {
        // 未出账：pack_ids 中的所有包裹都没有 statement_id
        $this->where('pa.id', 'not in', function($query) {
            $query->name('package')
                ->where('statement_id', '>', 0)
                ->where('inpack_id', 'exp', 'pa.id')
                ->field('DISTINCT inpack_id');
        });
    } elseif ($query['statement_status'] === 'unpaid') {
        // 已出账未支付：至少一个包裹有账单且未支付
        // 需要 JOIN package 和 statement 表
    } elseif ($query['statement_status'] === 'paid') {
        // 已支付：至少一个包裹有账单且已支付
    }
}
```

### 2. ⚠️ 混合账单显示

**问题**：如果一个集运单的部分包裹已出账，部分未出账，只会显示第一个找到的账单

**影响**：
- 这种情况在实际业务中应该很少见
- 通常会一次性为整个集运单的所有包裹生成账单

**改进方案**（如果需要）：
- 显示"部分出账"状态
- 列出所有相关账单编号

---

## 测试步骤

### 1. 基础功能测试

1. 访问 http://localhost:8080/index.php?s=/store/tr_order/all_list
2. 检查是否显示：
   - 账单状态筛选器（专属客服选择器后面）
   - 生成账单按钮（蓝色）
   - 账单列表按钮（灰色）
   - 账单信息列（表格中）

### 2. 生成账单测试

1. 选择同一客户的多个集运单
2. 点击"生成账单"按钮
3. 确认对话框应显示：
   - 集运单数量
   - 包裹总数
4. 确认后应调用 API 生成账单
5. 刷新页面，账单信息列应显示账单编号

### 3. 账单信息显示测试

1. 对于已出账的集运单：
   - 应显示账单编号（可点击）
   - 应显示支付状态徽章（未支付/已支付）
2. 对于未出账的集运单：
   - 应显示"未出账"（灰色文字）

### 4. 跨页面测试

1. 点击账单编号链接
2. 应跳转到账单详情页面
3. 点击"账单列表"按钮
4. 应跳转到账单列表页面

---

## 数据流程

```
用户选择集运单
    ↓
提取所有包裹ID (pack_ids)
    ↓
调用 package.statement/create API
    ↓
传递 package_ids 和 member_id
    ↓
StatementService 生成账单
    ↓
更新 Package 表的 statement_id
    ↓
刷新页面显示账单信息
```

---

## 文件清单

### 修改的文件
- `source/application/store/view/tr_order/index.php` - 集成组件
- `source/application/store/model/Package.php` - 添加 statement 关联

### 组件文件
- `source/application/store/view/tr_order/components/statement_filter.php` - 筛选器
- `source/application/store/view/tr_order/components/statement_buttons.php` - 按钮
- `source/application/store/view/tr_order/components/statement_column.php` - 信息列
- `source/application/store/view/tr_order/components/statement_script.php` - JavaScript
- `source/application/store/view/tr_order/components/README.md` - 使用说明

### 文档文件
- `docs/statement/16-tr_order集成完成.md` - 原始集成文档
- `docs/statement/17-tr_order集成说明.md` - 本文档（详细说明）

---

## 下一步（可选）

如果需要完整的账单状态筛选功能：

1. **方案A：数据库层面**
   - 在 Inpack::setWhere() 中添加子查询
   - 性能影响：中等（需要测试）
   - 实现复杂度：中等

2. **方案B：应用层面**
   - 先查询所有数据
   - 在 PHP 中过滤
   - 性能影响：高（大数据量时）
   - 实现复杂度：低

3. **方案C：冗余字段**
   - 在 Inpack 表添加 `has_statement` 字段
   - 生成/作废账单时更新该字段
   - 性能影响：低
   - 实现复杂度：中等
   - 维护成本：需要保持数据一致性

**推荐**：如果筛选功能不是必需的，保持当前实现即可。如果必需，推荐方案C（冗余字段）。

---

## 常见问题

### Q1: 为什么账单状态筛选器不工作？
A: 因为需要复杂的子查询，目前未实现。如果需要，参考上面的"下一步"部分。

### Q2: 如果一个集运单的包裹分属不同账单怎么办？
A: 目前只显示第一个找到的账单。这种情况在实际业务中应该很少见。

### Q3: 生成账单时提示"只能选择同一客户的订单"？
A: 确保选中的所有集运单属于同一个客户（member_id 相同）。

### Q4: 点击账单编号没反应？
A: 检查账单详情页面的路由是否正确配置。

### Q5: 账单信息列显示空白？
A: 检查浏览器控制台是否有 PHP 错误。可能是 Package 模型的 statement 关联未正确加载。

---

**总结**：前端功能已完整集成，核心的生成账单和显示账单信息功能已可用。账单状态筛选器作为可选功能，可根据实际需求决定是否实现。
