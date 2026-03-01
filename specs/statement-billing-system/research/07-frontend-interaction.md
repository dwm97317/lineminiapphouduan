# 前端交互设计研究

## 概述

账单系统的前端交互需要简洁高效，让财务人员能够快速完成订单选择、账单生成和管理操作。

## 技术栈

基于现有项目：
- jQuery 3.x
- Amaze UI（国产响应式框架）
- ECharts（图表）
- Datetimepicker（日期选择）
- IconFont（图标）

## 核心交互场景

### 1. 订单选择与账单生成

#### 1.1 页面布局

```
┌─────────────────────────────────────────────────┐
│ 筛选区域                                         │
│ [客户选择▼] [日期范围] [状态▼] [搜索]           │
└─────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────┐
│ 操作栏                                           │
│ [☑全选] 已选 0 个订单，共 0.00 KG               │
│ [生成账单] [导出Excel]                           │
└─────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────┐
│ 订单列表                                         │
│ ☑ 订单号  客户  重量  状态  入库时间  操作       │
│ ☑ P001   张三  10KG  待出账  2026-02-20  [详情] │
│ ☑ P002   张三  15KG  待出账  2026-02-21  [详情] │
│ □ P003   李四  8KG   待出账  2026-02-22  [详情] │
└─────────────────────────────────────────────────┘
```

#### 1.2 交互流程


**步骤1：选择客户**
```javascript
// 客户选择后，自动筛选该客户的待出账订单
$('#member-select').on('change', function() {
    var memberId = $(this).val();
    loadPackages(memberId);
    updateSelectedInfo();
});
```

**步骤2：勾选订单**
```javascript
// 单个勾选
$('.package-checkbox').on('change', function() {
    updateSelectedInfo();
    validateSelection();
});

// 全选/取消全选
$('#select-all').on('change', function() {
    var checked = $(this).prop('checked');
    $('.package-checkbox').prop('checked', checked);
    updateSelectedInfo();
});
```

**步骤3：实时计算**
```javascript
function updateSelectedInfo() {
    var selected = $('.package-checkbox:checked');
    var count = selected.length;
    var totalWeight = 0;
    
    selected.each(function() {
        var weight = parseFloat($(this).data('weight'));
        totalWeight += weight;
    });
    
    $('#selected-count').text(count);
    $('#selected-weight').text(totalWeight.toFixed(2));
}
```

**步骤4：验证并生成**
```javascript
$('#btn-generate').on('click', function() {
    var selected = $('.package-checkbox:checked');
    
    if (selected.length === 0) {
        layer.msg('请至少选择一个订单');
        return;
    }
    
    // 验证是否同一客户
    var memberIds = [];
    selected.each(function() {
        memberIds.push($(this).data('member-id'));
    });
    
    if (new Set(memberIds).size > 1) {
        layer.msg('只能选择同一个客户的订单');
        return;
    }
    
    // 确认生成
    layer.confirm('确定为选中的 ' + selected.length + ' 个订单生成账单？', {
        btn: ['确定', '取消']
    }, function(index) {
        generateStatement();
        layer.close(index);
    });
});
```

### 2. 账单列表与管理

#### 2.1 列表布局

```
┌─────────────────────────────────────────────────┐
│ 筛选区域                                         │
│ [客户▼] [日期范围] [支付状态▼] [搜索]           │
└─────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────┐
│ 账单列表                                         │
│ 账单号        客户  订单数  金额    状态  操作   │
│ ST20260228001 张三  5个    2300元  未支付 [...]  │
│ ST20260227001 李四  3个    1500元  已支付 [...]  │
└─────────────────────────────────────────────────┘
```

#### 2.2 操作菜单

```javascript
// 操作下拉菜单
<div class="btn-group">
    <button class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown">
        操作 <span class="caret"></span>
    </button>
    <ul class="dropdown-menu">
        <li><a href="javascript:;" onclick="viewDetail(id)">查看详情</a></li>
        <li><a href="javascript:;" onclick="downloadExcel(id)">下载Excel</a></li>
        <li><a href="javascript:;" onclick="markAsPaid(id)">标记已支付</a></li>
        <li><a href="javascript:;" onclick="voidStatement(id)">作废账单</a></li>
    </ul>
</div>
```

### 3. 账单详情页

#### 3.1 页面结构

```
┌─────────────────────────────────────────────────┐
│ 账单信息卡片                                     │
│ 账单编号：ST20260228001                          │
│ 客户：张三                                       │
│ 生成时间：2026-02-28 10:30:00                   │
│ 订单数量：5个                                    │
│ 总重量：50.5 KG                                  │
│ 总金额：2,323.00 元                              │
│ 支付状态：未支付                                 │
│ [下载Excel] [标记已支付] [作废]                  │
└─────────────────────────────────────────────────┘
┌─────────────────────────────────────────────────┐
│ 订单明细表格                                     │
│ 订单号  重量  单价  金额  入库时间               │
│ P001   10KG  46元  460元  2026-02-20            │
│ P002   15KG  46元  690元  2026-02-21            │
│ ...                                              │
└─────────────────────────────────────────────────┘
```

#### 3.2 标记已支付

```javascript
function markAsPaid(statementId) {
    layer.prompt({
        title: '标记为已支付',
        formType: 2, // 文本域
        value: '',
        placeholder: '请输入备注（可选）'
    }, function(remark, index) {
        $.ajax({
            url: '/store/statement/markPaid',
            type: 'POST',
            data: {
                statement_id: statementId,
                remark: remark
            },
            success: function(res) {
                if (res.code === 1) {
                    layer.msg('操作成功');
                    location.reload();
                } else {
                    layer.msg(res.msg);
                }
            }
        });
        layer.close(index);
    });
}
```

### 4. 财务配置页面

#### 4.1 Tab切换

```html
<ul class="nav nav-tabs">
    <li class="active"><a href="#tab-price" data-toggle="tab">计价配置</a></li>
    <li><a href="#tab-template" data-toggle="tab">模板配置</a></li>
    <li><a href="#tab-history" data-toggle="tab">历史单价</a></li>
</ul>
```

#### 4.2 计价方式选择

```javascript
// 计价方式切换
$('input[name="price_type"]').on('change', function() {
    var type = $(this).val();
    
    // 隐藏所有配置区域
    $('.price-config').hide();
    
    // 显示对应配置
    $('#config-' + type).show();
});
```

#### 4.3 阶梯价格配置

```html
<div id="config-tier" class="price-config">
    <table class="table">
        <thead>
            <tr>
                <th>最小重量(KG)</th>
                <th>最大重量(KG)</th>
                <th>单价(元/KG)</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody id="tier-list">
            <tr>
                <td><input type="number" name="tier_min[]" value="0" /></td>
                <td><input type="number" name="tier_max[]" value="10" /></td>
                <td><input type="number" name="tier_price[]" value="50" /></td>
                <td><button class="btn-remove">删除</button></td>
            </tr>
        </tbody>
    </table>
    <button id="btn-add-tier" class="btn btn-sm btn-primary">添加阶梯</button>
</div>
```

```javascript
// 添加阶梯
$('#btn-add-tier').on('click', function() {
    var row = `
        <tr>
            <td><input type="number" name="tier_min[]" /></td>
            <td><input type="number" name="tier_max[]" /></td>
            <td><input type="number" name="tier_price[]" /></td>
            <td><button class="btn-remove">删除</button></td>
        </tr>
    `;
    $('#tier-list').append(row);
});

// 删除阶梯
$(document).on('click', '.btn-remove', function() {
    $(this).closest('tr').remove();
});
```

### 5. 历史单价导入

#### 5.1 文件上传

```html
<div class="upload-area">
    <input type="file" id="file-history-price" accept=".txt,.xls,.xlsx" />
    <button id="btn-upload" class="btn btn-primary">上传并导入</button>
</div>

<div class="help-text">
    <p>支持格式：TXT、Excel</p>
    <p>TXT格式：每行一条，格式为"客户ID 单价"（空格或Tab分隔）</p>
    <p>Excel格式：A列客户ID，B列单价，无需表头</p>
</div>
```

```javascript
$('#btn-upload').on('click', function() {
    var file = $('#file-history-price')[0].files[0];
    
    if (!file) {
        layer.msg('请选择文件');
        return;
    }
    
    var formData = new FormData();
    formData.append('file', file);
    
    $.ajax({
        url: '/store/finance/importHistoryPrice',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.code === 1) {
                layer.msg('导入成功：' + res.data.success_count + ' 条');
                if (res.data.failed_count > 0) {
                    layer.alert('失败 ' + res.data.failed_count + ' 条：' + res.data.errors.join('<br>'));
                }
            } else {
                layer.msg(res.msg);
            }
        }
    });
});
```

### 6. 实时预览与验证

#### 6.1 公式实时验证

```javascript
// 自定义公式输入
$('#price-formula').on('input', function() {
    var formula = $(this).val();
    validateFormula(formula);
});

function validateFormula(formula) {
    $.ajax({
        url: '/store/finance/validateFormula',
        type: 'POST',
        data: { formula: formula },
        success: function(res) {
            if (res.code === 1) {
                $('#formula-status').html('<span class="text-success">✓ 公式有效</span>');
                $('#formula-result').text('测试结果（10KG）：' + res.data.test_result + ' 元');
            } else {
                $('#formula-status').html('<span class="text-danger">✗ ' + res.msg + '</span>');
            }
        }
    });
}
```

#### 6.2 金额实时计算

```javascript
// 订单选择时实时计算预估金额
function calculateEstimatedAmount(packageIds, memberId) {
    $.ajax({
        url: '/store/statement/calculateAmount',
        type: 'POST',
        data: {
            package_ids: packageIds,
            member_id: memberId
        },
        success: function(res) {
            if (res.code === 1) {
                $('#estimated-amount').text(res.data.total_amount.toFixed(2));
                
                // 显示明细
                var html = '';
                res.data.packages.forEach(function(pkg) {
                    html += '<tr>';
                    html += '<td>' + pkg.package_no + '</td>';
                    html += '<td>' + pkg.weight + ' KG</td>';
                    html += '<td>' + pkg.unit_price + ' 元/KG</td>';
                    html += '<td>' + pkg.amount + ' 元</td>';
                    html += '</tr>';
                });
                $('#preview-table tbody').html(html);
            }
        }
    });
}
```

## UI组件封装

### 1. 客户选择器

```javascript
// 初始化客户选择器（支持搜索）
function initMemberSelector(selector) {
    $(selector).select2({
        placeholder: '请选择客户',
        ajax: {
            url: '/store/member/search',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function(data) {
                return {
                    results: data.data.map(function(item) {
                        return {
                            id: item.id,
                            text: item.name + ' (' + item.mobile + ')'
                        };
                    })
                };
            }
        }
    });
}
```

### 2. 日期范围选择器

```javascript
// 初始化日期范围选择
function initDateRangePicker(selector) {
    $(selector).daterangepicker({
        locale: {
            format: 'YYYY-MM-DD',
            separator: ' 至 ',
            applyLabel: '确定',
            cancelLabel: '取消',
            fromLabel: '从',
            toLabel: '到',
            customRangeLabel: '自定义',
            weekLabel: 'W',
            daysOfWeek: ['日', '一', '二', '三', '四', '五', '六'],
            monthNames: ['1月', '2月', '3月', '4月', '5月', '6月', '7月', '8月', '9月', '10月', '11月', '12月']
        },
        ranges: {
            '今天': [moment(), moment()],
            '昨天': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
            '最近7天': [moment().subtract(6, 'days'), moment()],
            '最近30天': [moment().subtract(29, 'days'), moment()],
            '本月': [moment().startOf('month'), moment().endOf('month')],
            '上月': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
        }
    });
}
```

## 响应式设计

### 1. 移动端适配

```css
/* 移动端订单列表 */
@media (max-width: 768px) {
    .package-table {
        display: block;
        overflow-x: auto;
    }
    
    .package-table th,
    .package-table td {
        white-space: nowrap;
    }
    
    /* 操作按钮堆叠 */
    .action-buttons {
        display: flex;
        flex-direction: column;
        gap: 5px;
    }
}
```

### 2. 触摸优化

```javascript
// 移动端长按选择
var longPressTimer;
$('.package-row').on('touchstart', function() {
    var $row = $(this);
    longPressTimer = setTimeout(function() {
        $row.find('.package-checkbox').prop('checked', true).trigger('change');
    }, 500);
});

$('.package-row').on('touchend touchcancel', function() {
    clearTimeout(longPressTimer);
});
```

## 性能优化

### 1. 列表分页加载

```javascript
// 滚动加载更多
var page = 1;
var loading = false;

$(window).on('scroll', function() {
    if (loading) return;
    
    var scrollTop = $(window).scrollTop();
    var windowHeight = $(window).height();
    var documentHeight = $(document).height();
    
    if (scrollTop + windowHeight >= documentHeight - 100) {
        loadMorePackages();
    }
});

function loadMorePackages() {
    loading = true;
    page++;
    
    $.ajax({
        url: '/store/package/list',
        data: { page: page },
        success: function(res) {
            if (res.data.list.length > 0) {
                appendPackages(res.data.list);
            }
            loading = false;
        }
    });
}
```

### 2. 防抖与节流

```javascript
// 搜索防抖
var searchTimer;
$('#search-input').on('input', function() {
    clearTimeout(searchTimer);
    var keyword = $(this).val();
    
    searchTimer = setTimeout(function() {
        searchPackages(keyword);
    }, 500);
});

// 滚动节流
var scrollTimer;
$(window).on('scroll', function() {
    if (scrollTimer) return;
    
    scrollTimer = setTimeout(function() {
        handleScroll();
        scrollTimer = null;
    }, 200);
});
```

## 错误处理

### 1. 友好的错误提示

```javascript
// 统一错误处理
function handleError(error) {
    if (error.code === 401) {
        layer.msg('登录已过期，请重新登录');
        setTimeout(function() {
            location.href = '/store/login';
        }, 1500);
    } else if (error.code === 403) {
        layer.msg('没有权限执行此操作');
    } else {
        layer.msg(error.msg || '操作失败，请重试');
    }
}
```

### 2. 加载状态

```javascript
// 显示加载中
function showLoading(message) {
    return layer.load(1, {
        shade: [0.3, '#000'],
        content: message || '加载中...'
    });
}

// 隐藏加载
function hideLoading(index) {
    layer.close(index);
}

// 使用示例
var loadingIndex = showLoading('正在生成账单...');
generateStatement().then(function() {
    hideLoading(loadingIndex);
});
```

## 结论

前端交互设计要点：
- ✅ 简洁直观的操作流程
- ✅ 实时反馈和验证
- ✅ 友好的错误提示
- ✅ 响应式设计（支持移动端）
- ✅ 性能优化（分页、防抖、节流）
- ✅ 组件化封装（提高复用性）

**关键交互**：
1. 客户选择 → 订单筛选 → 勾选 → 实时计算 → 生成账单
2. 账单列表 → 查看详情 → 下载Excel → 标记支付 → 作废
3. 财务配置 → 计价方式 → 实时验证 → 保存

**用户体验**：
- 操作步骤少（3步完成账单生成）
- 反馈及时（实时计算、验证）
- 容错性好（友好提示、可撤销）
