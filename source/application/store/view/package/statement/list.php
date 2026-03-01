<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">账单列表</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 筛选工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" id="searchForm">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
                                    <!-- 客户选择 -->
                                    <div class="am-form-group am-fl">
                                        <select name="member_id" id="member_id" data-am-selected="{btnSize: 'sm', placeholder: '选择客户', searchBox: 1}">
                                            <option value="">全部客户</option>
                                        </select>
                                    </div>
                                    
                                    <!-- 支付状态 -->
                                    <div class="am-form-group am-fl">
                                        <select name="pay_status" data-am-selected="{btnSize: 'sm', placeholder: '支付状态'}">
                                            <option value="">全部</option>
                                            <option value="1">未支付</option>
                                            <option value="2">已支付</option>
                                        </select>
                                    </div>
                                    
                                    <!-- 账单状态 -->
                                    <div class="am-form-group am-fl">
                                        <select name="status" data-am-selected="{btnSize: 'sm', placeholder: '账单状态'}">
                                            <option value="">全部</option>
                                            <option value="1">正常</option>
                                            <option value="2">已作废</option>
                                        </select>
                                    </div>
                                    
                                    <!-- 日期范围 -->
                                    <div class="am-form-group am-fl">
                                        <input type="text" name="start_date" class="am-form-field" placeholder="开始日期" readonly>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <input type="text" name="end_date" class="am-form-field" placeholder="结束日期" readonly>
                                    </div>
                                    
                                    <!-- 关键词搜索 -->
                                    <div class="am-form-group am-fl">
                                        <input type="text" name="keyword" class="am-form-field" placeholder="账单编号/客户姓名">
                                    </div>
                                    
                                    <!-- 搜索按钮 -->
                                    <div class="am-form-group am-fl">
                                        <button type="submit" class="am-btn am-btn-primary am-btn-sm">
                                            <i class="am-icon-search"></i> 搜索
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- 统计信息 -->
                    <div class="am-margin-bottom-sm" id="statistics">
                        <span class="am-badge am-badge-primary">总账单：<span id="stat_total">0</span></span>
                        <span class="am-badge am-badge-success">已支付：<span id="stat_paid">0</span></span>
                        <span class="am-badge am-badge-warning">未支付：<span id="stat_unpaid">0</span></span>
                        <span class="am-badge am-badge-secondary">已作废：<span id="stat_void">0</span></span>
                        <span class="am-badge am-badge-primary">总金额：¥<span id="stat_amount">0.00</span></span>
                    </div>

                    <!-- 账单列表 -->
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table class="am-table am-table-striped am-table-hover am-table-bordered">
                            <thead>
                            <tr>
                                <th>账单编号</th>
                                <th>客户</th>
                                <th>订单数</th>
                                <th>总重量(KG)</th>
                                <th>总金额(元)</th>
                                <th>支付状态</th>
                                <th>账单状态</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody id="statementList">
                            <!-- 动态填充 -->
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr" id="pagination"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/amazeui.min.js"></script>
<script>
$(function() {
    // 初始化日期选择器
    $('input[name="start_date"], input[name="end_date"]').datetimepicker({
        format: 'yyyy-mm-dd',
        minView: 'month',
        language: 'zh-CN',
        autoclose: true
    });
    
    // 加载客户列表
    loadMemberList();
    
    // 加载统计数据
    loadStatistics();
    
    // 加载账单列表
    loadStatementList(1);
    
    // 搜索表单提交
    $('#searchForm').on('submit', function(e) {
        e.preventDefault();
        loadStatementList(1);
    });
});

// 加载客户列表
function loadMemberList() {
    $.ajax({
        url: '<?= url("user.user/getMemberList") ?>',
        success: function(res) {
            if (res.code == 1 && res.data.list) {
                var html = '<option value="">全部客户</option>';
                $.each(res.data.list, function(i, item) {
                    html += '<option value="' + item.user_id + '">' + item.nickName + '</option>';
                });
                $('#member_id').html(html);
            }
        }
    });
}

// 加载统计数据
function loadStatistics() {
    var params = $('#searchForm').serialize();
    $.ajax({
        url: '<?= url("package.statement/statistics") ?>',
        data: params,
        success: function(res) {
            if (res.code == 1) {
                $('#stat_total').text(res.data.total_count || 0);
                $('#stat_paid').text(res.data.paid_count || 0);
                $('#stat_unpaid').text(res.data.unpaid_count || 0);
                $('#stat_void').text(res.data.void_count || 0);
                $('#stat_amount').text((res.data.total_amount || 0).toFixed(2));
            }
        }
    });
}

// 加载账单列表
function loadStatementList(page) {
    var params = $('#searchForm').serialize() + '&page=' + page;
    
    $.ajax({
        url: '<?= url("package.statement/getList") ?>',
        data: params,
        success: function(res) {
            if (res.code == 1) {
                renderStatementList(res.data.list);
                renderPagination(res.data.total, page, res.data.page_size);
            }
        }
    });
}

// 渲染账单列表
function renderStatementList(list) {
    var html = '';
    
    if (list.length == 0) {
        html = '<tr><td colspan="9" class="am-text-center">暂无数据</td></tr>';
    } else {
        $.each(list, function(i, item) {
            html += '<tr>';
            html += '<td>' + item.statement_no + '</td>';
            html += '<td>' + item.member_name + '</td>';
            html += '<td>' + item.total_packages + '</td>';
            html += '<td>' + item.total_weight + '</td>';
            html += '<td class="am-text-danger">¥' + item.total_amount + '</td>';
            html += '<td>';
            if (item.pay_status == 2) {
                html += '<span class="am-badge am-badge-success">已支付</span>';
            } else {
                html += '<span class="am-badge am-badge-warning">未支付</span>';
            }
            html += '</td>';
            html += '<td>';
            if (item.status == 2) {
                html += '<span class="am-badge am-badge-secondary">已作废</span>';
            } else {
                html += '<span class="am-badge am-badge-primary">正常</span>';
            }
            html += '</td>';
            html += '<td>' + item.create_time + '</td>';
            html += '<td>';
            html += '<div class="am-btn-toolbar">';
            html += '<div class="am-btn-group am-btn-group-xs">';
            html += '<a href="javascript:;" class="am-btn am-btn-default am-btn-xs" onclick="viewDetail(' + item.id + ')">查看</a>';
            
            if (item.excel_path) {
                html += '<a href="<?= url("package.statement/downloadExcel") ?>/statement_id/' + item.id + '" class="am-btn am-btn-default am-btn-xs">下载</a>';
            }
            
            if (item.status == 1 && item.pay_status == 1) {
                html += '<a href="javascript:;" class="am-btn am-btn-success am-btn-xs" onclick="markPaid(' + item.id + ')">标记支付</a>';
                html += '<a href="javascript:;" class="am-btn am-btn-danger am-btn-xs" onclick="voidStatement(' + item.id + ')">作废</a>';
            }
            
            html += '</div>';
            html += '</div>';
            html += '</td>';
            html += '</tr>';
        });
    }
    
    $('#statementList').html(html);
}

// 渲染分页
function renderPagination(total, currentPage, pageSize) {
    var totalPages = Math.ceil(total / pageSize);
    var html = '';
    
    if (totalPages > 1) {
        html += '<ul class="am-pagination">';
        
        // 上一页
        if (currentPage > 1) {
            html += '<li><a href="javascript:;" onclick="loadStatementList(' + (currentPage - 1) + ')">«</a></li>';
        }
        
        // 页码
        for (var i = 1; i <= totalPages; i++) {
            if (i == currentPage) {
                html += '<li class="am-active"><a href="javascript:;">' + i + '</a></li>';
            } else {
                html += '<li><a href="javascript:;" onclick="loadStatementList(' + i + ')">' + i + '</a></li>';
            }
        }
        
        // 下一页
        if (currentPage < totalPages) {
            html += '<li><a href="javascript:;" onclick="loadStatementList(' + (currentPage + 1) + ')">»</a></li>';
        }
        
        html += '</ul>';
    }
    
    $('#pagination').html(html);
}

// 查看详情
function viewDetail(statementId) {
    window.location.href = '<?= url("package.statement/detail") ?>?statement_id=' + statementId;
}

// 标记已支付
function markPaid(statementId) {
    layer.prompt({
        title: '请输入支付备注',
        formType: 2
    }, function(remark, index) {
        $.ajax({
            url: '<?= url("package.statement/markPaid") ?>',
            type: 'POST',
            data: {
                statement_id: statementId,
                remark: remark
            },
            success: function(res) {
                if (res.code == 1) {
                    layer.msg('操作成功');
                    layer.close(index);
                    loadStatementList(1);
                    loadStatistics();
                } else {
                    layer.msg(res.msg);
                }
            }
        });
    });
}

// 作废账单
function voidStatement(statementId) {
    layer.confirm('确定要作废此账单吗？', function(index) {
        $.ajax({
            url: '<?= url("package.statement/void") ?>',
            type: 'POST',
            data: {
                statement_id: statementId
            },
            success: function(res) {
                if (res.code == 1) {
                    layer.msg('账单已作废');
                    layer.close(index);
                    loadStatementList(1);
                    loadStatistics();
                } else {
                    layer.msg(res.msg);
                }
            }
        });
    });
}
</script>
