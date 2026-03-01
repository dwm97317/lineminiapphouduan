<script>
// 账单列表组件脚本
(function() {
    var currentPage = 1;
    
    // 初始化账单列表
    window.initStatementList = function() {
        // 初始化日期选择器
        $('.statement-date').datetimepicker({
            format: 'yyyy-mm-dd',
            minView: 'month',
            language: 'zh-CN',
            autoclose: true
        });
        
        // 加载客户列表
        loadStatementMemberList();
        
        // 加载统计数据
        loadStatementStatistics();
        
        // 加载账单列表
        loadStatementList(1);
        
        // 搜索表单提交
        $('#statementSearchForm').on('submit', function(e) {
            e.preventDefault();
            loadStatementList(1);
        });
    };
    
    // 加载客户列表
    function loadStatementMemberList() {
        $.ajax({
            url: '<?= url("user.user/getMemberList") ?>',
            success: function(res) {
                if (res.code == 1 && res.data.list) {
                    var html = '<option value="">全部客户</option>';
                    $.each(res.data.list, function(i, item) {
                        html += '<option value="' + item.user_id + '">' + item.nickName + '</option>';
                    });
                    $('#statement_member_id').html(html);
                }
            }
        });
    }
    
    // 加载统计数据
    function loadStatementStatistics() {
        var params = $('#statementSearchForm').serialize();
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
        currentPage = page;
        var params = $('#statementSearchForm').serialize() + '&page=' + page;
        
        $.ajax({
            url: '<?= url("package.statement/getList") ?>',
            data: params,
            success: function(res) {
                if (res.code == 1) {
                    renderStatementList(res.data.list);
                    renderStatementPagination(res.data.total, page, res.data.page_size);
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
                html += '<a href="<?= url("package.statement/detail") ?>?statement_id=' + item.id + '" class="am-btn am-btn-default am-btn-xs">查看</a>';
                
                if (item.excel_path) {
                    html += '<a href="<?= url("package.statement/downloadExcel") ?>/statement_id/' + item.id + '" class="am-btn am-btn-default am-btn-xs">下载</a>';
                }
                
                if (item.status == 1 && item.pay_status == 1) {
                    html += '<a href="javascript:;" class="am-btn am-btn-success am-btn-xs" onclick="markStatementPaid(' + item.id + ')">标记支付</a>';
                    html += '<a href="javascript:;" class="am-btn am-btn-danger am-btn-xs" onclick="voidStatement(' + item.id + ')">作废</a>';
                }
                
                html += '</div>';
                html += '</div>';
                html += '</td>';
                html += '</tr>';
            });
        }
        
        $('#statementListBody').html(html);
    }
    
    // 渲染分页
    function renderStatementPagination(total, currentPage, pageSize) {
        var totalPages = Math.ceil(total / pageSize);
        var html = '';
        
        if (totalPages > 1) {
            html += '<ul class="am-pagination">';
            
            // 上一页
            if (currentPage > 1) {
                html += '<li><a href="javascript:;" onclick="loadStatementListPage(' + (currentPage - 1) + ')">«</a></li>';
            }
            
            // 页码
            for (var i = 1; i <= totalPages; i++) {
                if (i == currentPage) {
                    html += '<li class="am-active"><a href="javascript:;">' + i + '</a></li>';
                } else {
                    html += '<li><a href="javascript:;" onclick="loadStatementListPage(' + i + ')">' + i + '</a></li>';
                }
            }
            
            // 下一页
            if (currentPage < totalPages) {
                html += '<li><a href="javascript:;" onclick="loadStatementListPage(' + (currentPage + 1) + ')">»</a></li>';
            }
            
            html += '</ul>';
        }
        
        $('#statement_pagination').html(html);
    }
    
    // 全局函数：加载指定页
    window.loadStatementListPage = function(page) {
        loadStatementList(page);
    };
    
    // 标记已支付
    window.markStatementPaid = function(statementId) {
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
                        loadStatementList(currentPage);
                        loadStatementStatistics();
                    } else {
                        layer.msg(res.msg);
                    }
                }
            });
        });
    };
    
    // 作废账单
    window.voidStatement = function(statementId) {
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
                        loadStatementList(currentPage);
                        loadStatementStatistics();
                    } else {
                        layer.msg(res.msg);
                    }
                }
            });
        });
    };
})();
</script>
