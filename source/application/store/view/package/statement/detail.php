<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">账单详情</div>
                    <div class="widget-function">
                        <a href="<?= url('finance.config/index') ?>#tab-statement-list" class="am-btn am-btn-default am-btn-xs">
                            <i class="am-icon-reply"></i> 返回列表
                        </a>
                    </div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 账单信息卡片 -->
                    <div class="am-panel am-panel-default" id="statementInfo">
                        <div class="am-panel-hd">账单信息</div>
                        <div class="am-panel-bd">
                            <div class="am-g">
                                <div class="am-u-md-6">
                                    <p><strong>账单编号：</strong><span id="statement_no"></span></p>
                                    <p><strong>客户姓名：</strong><span id="member_name"></span></p>
                                    <p><strong>订单数量：</strong><span id="total_packages"></span></p>
                                    <p><strong>总重量：</strong><span id="total_weight"></span> KG</p>
                                </div>
                                <div class="am-u-md-6">
                                    <p><strong>总金额：</strong><span class="am-text-danger" id="total_amount"></span></p>
                                    <p><strong>支付状态：</strong><span id="pay_status"></span></p>
                                    <p><strong>账单状态：</strong><span id="status"></span></p>
                                    <p><strong>创建时间：</strong><span id="create_time"></span></p>
                                </div>
                            </div>
                            
                            <!-- 操作按钮 -->
                            <div class="am-margin-top" id="actionButtons">
                                <!-- 动态生成 -->
                            </div>
                        </div>
                    </div>

                    <!-- 订单明细表格 -->
                    <div class="am-panel am-panel-default">
                        <div class="am-panel-hd">订单明细</div>
                        <div class="am-panel-bd">
                            <div class="am-scrollable-horizontal">
                                <table class="am-table am-table-striped am-table-hover am-table-bordered">
                                    <thead>
                                    <tr>
                                        <th>序号</th>
                                        <th>订单编号</th>
                                        <th>重量(KG)</th>
                                        <th>单价(元/KG)</th>
                                        <th>金额(元)</th>
                                        <th>入库时间</th>
                                    </tr>
                                    </thead>
                                    <tbody id="packageList">
                                    <!-- 动态填充 -->
                                    </tbody>
                                    <tfoot>
                                    <tr class="am-text-danger">
                                        <td colspan="2" class="am-text-right"><strong>合计：</strong></td>
                                        <td><strong id="sum_weight"></strong></td>
                                        <td>-</td>
                                        <td><strong id="sum_amount"></strong></td>
                                        <td>-</td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="assets/common/js/amazeui.min.js"></script>
<script>
$(function() {
    // 直接使用 PHP 传递的数据
    var statement = <?= json_encode($statement ?? []) ?>;
    var packages = <?= json_encode($packages ?? []) ?>;
    
    if (statement && statement.id) {
        renderStatementInfo(statement);
        renderPackageList(packages);
    } else {
        layer.msg('账单数据加载失败');
    }
});

// 渲染账单信息
function renderStatementInfo(statement) {
    $('#statement_no').text(statement.statement_no);
    $('#member_name').text(statement.member_id || '-');  // 显示用户ID
    $('#total_packages').text(statement.total_packages);
    $('#total_weight').text(statement.total_weight);
    $('#total_amount').text('¥' + statement.total_amount);
    $('#create_time').text(statement.create_time);
    
    // 支付状态
    if (statement.pay_status == 2) {
        $('#pay_status').html('<span class="am-badge am-badge-success">已支付</span>');
        if (statement.pay_time) {
            $('#pay_status').append('<br><small>' + statement.pay_time + '</small>');
        }
    } else {
        $('#pay_status').html('<span class="am-badge am-badge-warning">未支付</span>');
    }
    
    // 账单状态
    if (statement.status == 2) {
        $('#status').html('<span class="am-badge am-badge-secondary">已作废</span>');
    } else {
        $('#status').html('<span class="am-badge am-badge-primary">正常</span>');
    }
    
    // 操作按钮
    var buttons = '';
    
    if (statement.excel_path) {
        buttons += '<a href="<?= url("package.statement/downloadExcel") ?>/statement_id/' + statement.id + '" class="am-btn am-btn-primary am-btn-sm">';
        buttons += '<i class="am-icon-download"></i> 下载Excel</a> ';
        
        buttons += '<a href="javascript:;" class="am-btn am-btn-default am-btn-sm" onclick="regenerateExcel(' + statement.id + ')">';
        buttons += '<i class="am-icon-refresh"></i> 重新生成Excel</a> ';
    }
    
    if (statement.status == 1 && statement.pay_status == 1) {
        buttons += '<a href="javascript:;" class="am-btn am-btn-success am-btn-sm" onclick="markPaid(' + statement.id + ')">';
        buttons += '<i class="am-icon-check"></i> 标记已支付</a> ';
        
        buttons += '<a href="javascript:;" class="am-btn am-btn-danger am-btn-sm" onclick="voidStatement(' + statement.id + ')">';
        buttons += '<i class="am-icon-close"></i> 作废账单</a>';
    }
    
    $('#actionButtons').html(buttons);
}

// 渲染订单列表
function renderPackageList(packages) {
    var html = '';
    var sumWeight = 0;
    var sumAmount = 0;
    
    $.each(packages, function(i, item) {
        // 使用 cale_weight (计费重量) 和 calculated_amount (计算金额)
        var weight = parseFloat(item.cale_weight || item.weight || 0);
        var amount = parseFloat(item.calculated_amount || item.amount || 0);
        var unitPrice = parseFloat(item.unit_price || 0);
        
        sumWeight += weight;
        sumAmount += amount;
        
        html += '<tr>';
        html += '<td>' + (i + 1) + '</td>';
        html += '<td>' + (item.t_order_sn || item.order_sn || '-') + '</td>';  // 使用国际运单号
        html += '<td>' + weight.toFixed(2) + '</td>';
        html += '<td>' + unitPrice.toFixed(2) + '</td>';
        html += '<td class="am-text-danger">¥' + amount.toFixed(2) + '</td>';
        html += '<td>' + (item.created_time || item.instore_time || '-') + '</td>';
        html += '</tr>';
    });
    
    $('#packageList').html(html);
    $('#sum_weight').text(sumWeight.toFixed(2));
    $('#sum_amount').text('¥' + sumAmount.toFixed(2));
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
                    location.reload();
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
                    location.reload();
                } else {
                    layer.msg(res.msg);
                }
            }
        });
    });
}

// 重新生成Excel
function regenerateExcel(statementId) {
    layer.confirm('确定要重新生成Excel吗？', function(index) {
        $.ajax({
            url: '<?= url("package.statement/regenerateExcel") ?>',
            type: 'POST',
            data: {
                statement_id: statementId
            },
            success: function(res) {
                if (res.code == 1) {
                    layer.msg('Excel重新生成成功');
                    layer.close(index);
                } else {
                    layer.msg(res.msg);
                }
            }
        });
    });
}
</script>
