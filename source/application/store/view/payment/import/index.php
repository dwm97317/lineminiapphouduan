<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">财务原始数据导入</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 文件上传区域 -->
                    <div class="am-panel am-panel-default am-margin-top">
                        <div class="am-panel-hd">上传Excel文件</div>
                        <div class="am-panel-bd">
                            <!-- Tab导航 -->
                            <div class="am-tabs" data-am-tabs>
                                <ul class="am-tabs-nav am-nav am-nav-tabs">
                                    <li class="am-active"><a href="#tab-upload">浏览器上传</a></li>
                                    <li><a href="#tab-server">从服务器读取</a></li>
                                </ul>

                                <div class="am-tabs-bd">
                                    <!-- 浏览器上传 -->
                                    <div class="am-tab-panel am-fade am-in am-active" id="tab-upload">
                                        <form class="am-form" id="uploadForm">
                                            <div class="am-form-group">
                                                <label>选择文件</label>
                                                <input type="file" name="file" id="importFile" accept=".xls,.xlsx">
                                                <small class="am-text-muted">支持.xls和.xlsx格式，最大10MB</small>
                                            </div>
                                            <div class="am-form-group">
                                                <button type="button" class="am-btn am-btn-primary" onclick="uploadFile()">
                                                    <i class="am-icon-upload"></i> 开始解析
                                                </button>
                                            </div>
                                        </form>
                                        <div class="am-alert am-alert-warning">
                                            <strong>注意：</strong>浏览器上传可能会丢失Excel格式信息，导致颜色识别不准确。建议使用"从服务器读取"方式。
                                        </div>
                                    </div>

                                    <!-- 从服务器读取 -->
                                    <div class="am-tab-panel am-fade" id="tab-server">
                                        <form class="am-form" id="serverForm">
                                            <div class="am-form-group">
                                                <label>文件名</label>
                                                <input type="text" name="file_name" id="serverFileName" class="am-form-field" placeholder="例如: test1111.xlsx">
                                                <small class="am-text-muted">请将Excel文件放到服务器的 uploads/temp/payment_import/ 目录</small>
                                            </div>
                                            <div class="am-form-group">
                                                <button type="button" class="am-btn am-btn-success" onclick="loadFromServer()">
                                                    <i class="am-icon-folder-open"></i> 从服务器读取
                                                </button>
                                            </div>
                                        </form>
                                        <div class="am-alert am-alert-success">
                                            <strong>推荐方式：</strong>直接从服务器读取文件可以保留完整的Excel格式信息，确保颜色识别准确。
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="am-alert am-alert-secondary am-margin-top">
                                <h4>Excel格式说明：</h4>
                                <p>列A：用户ID（可带国家后缀，如"23048泰国"）</p>
                                <p>列B：重量（KG）</p>
                                <p>列C：国际单号</p>
                                <p>列D：日期（可选，如"2月13日"）</p>
                                <p>行背景颜色：蓝色=已支付已出账，粉红色=已出账未支付，绿色=已支付未出账，白色=跳过</p>
                            </div>
                        </div>
                    </div>

                    <!-- 预览数据区域 -->
                    <div id="previewSection" style="display:none;">
                        <!-- 统计信息 -->
                        <div class="am-panel am-panel-primary am-margin-top">
                            <div class="am-panel-hd">导入统计</div>
                            <div class="am-panel-bd">
                                <div class="am-margin-bottom-sm" id="statistics">
                                    <span class="am-badge am-badge-primary">总行数：<span id="stat_total">0</span></span>
                                    <span class="am-badge" style="background-color:#4A90E2;">蓝色（已支付已出账）：<span id="stat_blue">0</span></span>
                                    <span class="am-badge" style="background-color:#FF69B4;">粉红色（已出账未支付）：<span id="stat_pink">0</span></span>
                                    <span class="am-badge am-badge-success">绿色（已支付未出账）：<span id="stat_green">0</span></span>
                                    <span class="am-badge am-badge-warning">未知颜色：<span id="stat_unknown">0</span></span>
                                    <span class="am-badge am-badge-secondary">未匹配订单：<span id="stat_unmatched">0</span></span>
                                    <span class="am-badge am-badge-danger">多重匹配：<span id="stat_multiple">0</span></span>
                                </div>

                                <!-- Sheet级别统计 -->
                                <div id="sheetStatistics" class="am-margin-top"></div>
                            </div>
                        </div>

                        <!-- 未知颜色修正区域 -->
                        <div id="unknownColorSection" class="am-panel am-panel-warning am-margin-top" style="display:none;">
                            <div class="am-panel-hd">未知颜色修正（需要手动选择）</div>
                            <div class="am-panel-bd">
                                <table class="am-table am-table-striped am-table-hover am-table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sheet</th>
                                            <th>行号</th>
                                            <th>用户ID</th>
                                            <th>重量</th>
                                            <th>国际单号</th>
                                            <th>RGB值</th>
                                            <th>选择颜色</th>
                                        </tr>
                                    </thead>
                                    <tbody id="unknownColorList"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 多重匹配选择区域 -->
                        <div id="multipleMatchSection" class="am-panel am-panel-danger am-margin-top" style="display:none;">
                            <div class="am-panel-hd">多重匹配订单（需要手动选择）</div>
                            <div class="am-panel-bd">
                                <div id="multipleMatchList"></div>
                            </div>
                        </div>

                        <!-- 未匹配订单列表 -->
                        <div id="unmatchedSection" class="am-panel am-panel-secondary am-margin-top" style="display:none;">
                            <div class="am-panel-hd">未匹配订单</div>
                            <div class="am-panel-bd">
                                <table class="am-table am-table-striped am-table-hover am-table-bordered">
                                    <thead>
                                        <tr>
                                            <th>Sheet</th>
                                            <th>行号</th>
                                            <th>用户ID</th>
                                            <th>重量</th>
                                            <th>国际单号</th>
                                            <th>日期</th>
                                            <th>颜色</th>
                                        </tr>
                                    </thead>
                                    <tbody id="unmatchedList"></tbody>
                                </table>
                            </div>
                        </div>

                        <!-- 预览数据展示（按颜色分组） -->
                        <div class="am-panel am-panel-default am-margin-top">
                            <div class="am-panel-hd">预览数据</div>
                            <div class="am-panel-bd">
                                <!-- Tab导航 -->
                                <div class="am-tabs" data-am-tabs>
                                    <ul class="am-tabs-nav am-nav am-nav-tabs">
                                        <li class="am-active"><a href="#tab-blue">蓝色订单</a></li>
                                        <li><a href="#tab-pink">粉红色订单</a></li>
                                        <li><a href="#tab-green">绿色订单</a></li>
                                    </ul>

                                    <div class="am-tabs-bd">
                                        <!-- 蓝色订单 -->
                                        <div class="am-tab-panel am-fade am-in am-active" id="tab-blue">
                                            <div class="am-scrollable-horizontal">
                                                <table class="am-table am-table-striped am-table-hover am-table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Sheet</th>
                                                            <th>行号</th>
                                                            <th>用户ID</th>
                                                            <th>重量</th>
                                                            <th>国际单号</th>
                                                            <th>匹配订单号</th>
                                                            <th>匹配类型</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="blueOrderList"></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- 粉红色订单 -->
                                        <div class="am-tab-panel am-fade" id="tab-pink">
                                            <div class="am-scrollable-horizontal">
                                                <table class="am-table am-table-striped am-table-hover am-table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Sheet</th>
                                                            <th>行号</th>
                                                            <th>用户ID</th>
                                                            <th>重量</th>
                                                            <th>国际单号</th>
                                                            <th>匹配订单号</th>
                                                            <th>匹配类型</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="pinkOrderList"></tbody>
                                                </table>
                                            </div>
                                        </div>

                                        <!-- 绿色订单 -->
                                        <div class="am-tab-panel am-fade" id="tab-green">
                                            <div class="am-scrollable-horizontal">
                                                <table class="am-table am-table-striped am-table-hover am-table-bordered">
                                                    <thead>
                                                        <tr>
                                                            <th>Sheet</th>
                                                            <th>行号</th>
                                                            <th>用户ID</th>
                                                            <th>重量</th>
                                                            <th>国际单号</th>
                                                            <th>匹配订单号</th>
                                                            <th>匹配类型</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody id="greenOrderList"></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 确认和取消按钮 -->
                        <div class="am-margin-top am-margin-bottom">
                            <button type="button" class="am-btn am-btn-success am-btn-lg" onclick="confirmImport()" id="confirmBtn">
                                <i class="am-icon-check"></i> 确认导入
                            </button>
                            <button type="button" class="am-btn am-btn-default am-btn-lg" onclick="cancelImport()">
                                <i class="am-icon-close"></i> 取消
                            </button>
                        </div>
                    </div>

                    <!-- 导入报告区域 -->
                    <div id="reportSection" style="display:none;">
                        <div class="am-panel am-panel-success am-margin-top">
                            <div class="am-panel-hd">导入报告</div>
                            <div class="am-panel-bd">
                                <div class="am-margin-bottom-sm" id="reportStatistics"></div>
                                
                                <!-- 失败的客户 -->
                                <div id="failedMembersSection" style="display:none;">
                                    <h4>失败的客户</h4>
                                    <table class="am-table am-table-striped am-table-bordered">
                                        <thead>
                                            <tr>
                                                <th>用户ID</th>
                                                <th>错误信息</th>
                                            </tr>
                                        </thead>
                                        <tbody id="failedMembersList"></tbody>
                                    </table>
                                </div>

                                <!-- 创建的历史账单 -->
                                <div id="createdStatementsSection" style="display:none;">
                                    <h4>创建的历史账单</h4>
                                    <table class="am-table am-table-striped am-table-bordered">
                                        <thead>
                                            <tr>
                                                <th>用户ID</th>
                                                <th>账单ID</th>
                                                <th>账单编号</th>
                                            </tr>
                                        </thead>
                                        <tbody id="createdStatementsList"></tbody>
                                    </table>
                                </div>

                                <div class="am-margin-top">
                                    <button type="button" class="am-btn am-btn-primary" onclick="location.reload()">
                                        <i class="am-icon-refresh"></i> 重新导入
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// 全局变量存储预览数据
var previewData = null;
var filePath = null;
var userCorrections = {
    color_corrections: {},
    order_selections: {}
};

// 上传文件
function uploadFile() {
    var fileInput = document.getElementById('importFile');
    var file = fileInput.files[0];
    
    if (!file) {
        layer.msg('请选择文件');
        return;
    }
    
    var formData = new FormData();
    formData.append('file', file);
    
    var loading = layer.load(1, {shade: [0.3, '#fff']});
    
    $.ajax({
        url: '<?= url("payment.import/upload") ?>',
        type: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function(res) {
            layer.close(loading);
            if (res.code == 1) {
                // 后端返回的data结构: {file_path, file_name, parsed_data}
                // parsed_data包含: {sheets: [...], total_rows: N}
                // 但我们需要调用preview接口来生成完整的预览数据
                var parsedData = res.data.parsed_data;
                
                // 调用preview接口生成预览数据
                generatePreview(parsedData, res.data.file_path);
            } else {
                layer.msg(res.msg || '解析失败');
            }
        },
        error: function() {
            layer.close(loading);
            layer.msg('网络错误');
        }
    });
}

// 从服务器读取文件
function loadFromServer() {
    var fileName = document.getElementById('serverFileName').value.trim();
    
    if (!fileName) {
        layer.msg('请输入文件名');
        return;
    }
    
    var loading = layer.load(1, {shade: [0.3, '#fff']});
    
    $.ajax({
        url: '<?= url("payment.import/loadFromServer") ?>',
        type: 'POST',
        data: {
            file_name: fileName
        },
        success: function(res) {
            layer.close(loading);
            if (res.code == 1) {
                var parsedData = res.data.parsed_data;
                
                // 调用preview接口生成预览数据
                generatePreview(parsedData, res.data.file_path);
            } else {
                layer.msg(res.msg || '读取失败');
            }
        },
        error: function() {
            layer.close(loading);
            layer.msg('网络错误');
        }
    });
}

// 生成预览数据
function generatePreview(parsedData, filePathValue) {
    var loading = layer.load(1, {shade: [0.3, '#fff']});
    
    $.ajax({
        url: '<?= url("payment.import/preview") ?>',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify({
            parsed_data: parsedData
        }),
        success: function(res) {
            layer.close(loading);
            if (res.code == 1) {
                previewData = res.data.preview_data;
                filePath = filePathValue;
                renderPreview(previewData);
                $('#previewSection').show();
                layer.msg('预览数据生成成功');
            } else {
                layer.msg(res.msg || '预览数据生成失败');
            }
        },
        error: function() {
            layer.close(loading);
            layer.msg('网络错误');
        }
    });
}

// 渲染预览数据
function renderPreview(data) {
    // 渲染统计信息
    $('#stat_total').text(data.statistics.total_rows || 0);
    $('#stat_blue').text(data.statistics.blue_count || 0);
    $('#stat_pink').text(data.statistics.pink_count || 0);
    $('#stat_green').text(data.statistics.green_count || 0);
    $('#stat_unknown').text(data.statistics.unknown_count || 0);
    $('#stat_unmatched').text(data.statistics.unmatched_count || 0);
    $('#stat_multiple').text(data.statistics.multiple_match_count || 0);
    
    // 渲染Sheet级别统计
    renderSheetStatistics(data.sheets);
    
    // 渲染未知颜色
    if (data.statistics.unknown_count > 0) {
        renderUnknownColors(data.rows_by_color.unknown || []);
        $('#unknownColorSection').show();
    }
    
    // 渲染多重匹配
    if (data.statistics.multiple_match_count > 0) {
        renderMultipleMatches(data.rows_by_color.multiple_match || []);
        $('#multipleMatchSection').show();
    }
    
    // 渲染未匹配订单
    if (data.statistics.unmatched_count > 0) {
        renderUnmatchedOrders(data.rows_by_color.unmatched || []);
        $('#unmatchedSection').show();
    }
    
    // 渲染各颜色订单列表
    renderColorOrders('blue', data.rows_by_color.blue || []);
    renderColorOrders('pink', data.rows_by_color.pink || []);
    renderColorOrders('green', data.rows_by_color.green || []);
    
    // 检查是否可以确认导入
    checkCanConfirm();
}

// 渲染Sheet统计
function renderSheetStatistics(sheets) {
    var html = '<table class="am-table am-table-bordered am-table-compact"><thead><tr>' +
        '<th>Sheet名称</th><th>总行数</th><th>蓝色</th><th>粉红色</th><th>绿色</th><th>白色</th><th>未知</th>' +
        '</tr></thead><tbody>';
    
    $.each(sheets, function(i, sheet) {
        html += '<tr>';
        html += '<td>' + sheet.name + '</td>';
        html += '<td>' + sheet.total_rows + '</td>';
        html += '<td>' + sheet.blue_count + '</td>';
        html += '<td>' + sheet.pink_count + '</td>';
        html += '<td>' + sheet.green_count + '</td>';
        html += '<td>' + sheet.white_count + '</td>';
        html += '<td>' + sheet.unknown_count + '</td>';
        html += '</tr>';
    });
    
    html += '</tbody></table>';
    $('#sheetStatistics').html(html);
}

// 渲染未知颜色
function renderUnknownColors(rows) {
    var html = '';
    $.each(rows, function(i, row) {
        var rowKey = row.sheet_name + '_' + row.row_number;
        html += '<tr>';
        html += '<td>' + row.sheet_name + '</td>';
        html += '<td>' + row.row_number + '</td>';
        html += '<td>' + row.member_id + '</td>';
        html += '<td>' + row.weight + '</td>';
        html += '<td>' + (row.express_num || '-') + '</td>';
        html += '<td>R:' + row.rgb.r + ' G:' + row.rgb.g + ' B:' + row.rgb.b + '</td>';
        html += '<td><select class="am-form-field" onchange="updateUnknownColor(\'' + rowKey + '\', this.value)">';
        html += '<option value="">请选择</option>';
        html += '<option value="blue">蓝色（已支付已出账）</option>';
        html += '<option value="pink">粉红色（已出账未支付）</option>';
        html += '<option value="green">绿色（已支付未出账）</option>';
        html += '<option value="skip">跳过</option>';
        html += '</select></td>';
        html += '</tr>';
    });
    $('#unknownColorList').html(html);
}

// 渲染多重匹配
function renderMultipleMatches(rows) {
    var html = '';
    $.each(rows, function(i, row) {
        var rowKey = row.sheet_name + '_' + row.row_number;
        html += '<div class="am-panel am-panel-default">';
        html += '<div class="am-panel-hd">Sheet: ' + row.sheet_name + ' 行: ' + row.row_number + 
                ' | 用户ID: ' + row.member_id + ' | 重量: ' + row.weight + ' | 国际单号: ' + (row.express_num || '-') + '</div>';
        html += '<div class="am-panel-bd">';
        html += '<table class="am-table am-table-bordered"><thead><tr><th width="50">选择</th><th>订单号</th><th>重量</th><th>创建时间</th></tr></thead><tbody>';
        
        $.each(row.candidates, function(j, candidate) {
            html += '<tr>';
            html += '<td><input type="radio" name="match_' + rowKey + '" value="' + candidate.id + '" onchange="updateMultipleMatch(\'' + rowKey + '\', ' + candidate.id + ')"></td>';
            html += '<td>' + candidate.order_sn + '</td>';
            html += '<td>' + candidate.weight + '</td>';
            html += '<td>' + candidate.created_time + '</td>';
            html += '</tr>';
        });
        
        html += '</tbody></table></div></div>';
    });
    $('#multipleMatchList').html(html);
}

// 渲染未匹配订单
function renderUnmatchedOrders(rows) {
    var html = '';
    $.each(rows, function(i, row) {
        html += '<tr>';
        html += '<td>' + row.sheet_name + '</td>';
        html += '<td>' + row.row_number + '</td>';
        html += '<td>' + row.member_id + '</td>';
        html += '<td>' + row.weight + '</td>';
        html += '<td>' + (row.express_num || '-') + '</td>';
        html += '<td>' + (row.date || '-') + '</td>';
        html += '<td>' + row.color + '</td>';
        html += '</tr>';
    });
    $('#unmatchedList').html(html);
}

// 渲染颜色订单列表
function renderColorOrders(color, rows) {
    var html = '';
    if (rows.length == 0) {
        html = '<tr><td colspan="7" class="am-text-center">暂无数据</td></tr>';
    } else {
        $.each(rows, function(i, row) {
            html += '<tr>';
            html += '<td>' + row.sheet_name + '</td>';
            html += '<td>' + row.row_number + '</td>';
            html += '<td>' + row.member_id + '</td>';
            html += '<td>' + row.weight + '</td>';
            html += '<td>' + (row.express_num || '-') + '</td>';
            html += '<td>' + (row.matched_order ? row.matched_order.order_sn : '-') + '</td>';
            html += '<td>' + (row.match_type || '-') + '</td>';
            html += '</tr>';
        });
    }
    $('#' + color + 'OrderList').html(html);
}

// 更新未知颜色选择
function updateUnknownColor(rowKey, color) {
    userCorrections.color_corrections[rowKey] = color;
    checkCanConfirm();
}

// 更新多重匹配选择
function updateMultipleMatch(rowKey, orderId) {
    userCorrections.order_selections[rowKey] = orderId;
    checkCanConfirm();
}

// 检查是否可以确认导入
function checkCanConfirm() {
    // 未知颜色的行直接跳过，不需要修正
    // 只检查多重匹配是否已全部选择
    var multipleCount = previewData.statistics.multiple_match_count || 0;
    
    // 如果没有多重匹配，或者所有多重匹配都已选择，则可以确认导入
    var multipleResolved = (multipleCount === 0) || (Object.keys(userCorrections.order_selections).length >= multipleCount);
    
    if (multipleResolved) {
        $('#confirmBtn').prop('disabled', false);
    } else {
        $('#confirmBtn').prop('disabled', true);
    }
}

// 确认导入
function confirmImport() {
    // 验证所有未知颜色和多重匹配已解决
    var unknownCount = previewData.statistics.unknown_count || 0;
    var multipleCount = previewData.statistics.multiple_match_count || 0;
    
    if (Object.keys(userCorrections.color_corrections).length < unknownCount) {
        layer.msg('请为所有未知颜色选择分类');
        return;
    }
    
    if (Object.keys(userCorrections.order_selections).length < multipleCount) {
        layer.msg('请为所有多重匹配选择订单');
        return;
    }
    
    layer.confirm('确定要导入数据吗？', function(index) {
        var loading = layer.load(1, {shade: [0.3, '#fff']});
        
        $.ajax({
            url: '<?= url("payment.import/confirm") ?>',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                preview_data: previewData,
                user_corrections: userCorrections,
                file_path: filePath
            }),
            success: function(res) {
                layer.close(loading);
                layer.close(index);
                
                if (res.code == 1) {
                    $('#previewSection').hide();
                    renderReport(res.data);
                    $('#reportSection').show();
                    layer.msg('导入完成');
                } else {
                    layer.msg(res.msg || '导入失败');
                }
            },
            error: function() {
                layer.close(loading);
                layer.close(index);
                layer.msg('网络错误');
            }
        });
    });
}

// 取消导入
function cancelImport() {
    layer.confirm('确定要取消导入吗？', function(index) {
        $.ajax({
            url: '<?= url("payment.import/cancel") ?>',
            type: 'POST',
            data: {
                file_path: filePath
            },
            success: function(res) {
                layer.close(index);
                location.reload();
            }
        });
    });
}

// 渲染导入报告
function renderReport(report) {
    var html = '';
    html += '<span class="am-badge am-badge-primary">总处理：<span>' + report.total_processed + '</span></span> ';
    html += '<span class="am-badge am-badge-success">成功：<span>' + report.success_count + '</span></span> ';
    html += '<span class="am-badge am-badge-danger">失败：<span>' + report.failure_count + '</span></span> ';
    html += '<span class="am-badge" style="background-color:#4A90E2;">蓝色处理：<span>' + report.statistics.blue_processed + '</span></span> ';
    html += '<span class="am-badge" style="background-color:#FF69B4;">粉红色处理：<span>' + report.statistics.pink_processed + '</span></span> ';
    html += '<span class="am-badge am-badge-success">绿色处理：<span>' + report.statistics.green_processed + '</span></span>';
    
    $('#reportStatistics').html(html);
    
    // 渲染失败的客户
    if (report.failed_members && report.failed_members.length > 0) {
        var failedHtml = '';
        $.each(report.failed_members, function(i, item) {
            failedHtml += '<tr>';
            failedHtml += '<td>' + item.member_id + '</td>';
            failedHtml += '<td>' + item.error + '</td>';
            failedHtml += '</tr>';
        });
        $('#failedMembersList').html(failedHtml);
        $('#failedMembersSection').show();
    }
    
    // 渲染创建的账单
    if (report.created_statements && report.created_statements.length > 0) {
        var statementsHtml = '';
        $.each(report.created_statements, function(i, item) {
            statementsHtml += '<tr>';
            statementsHtml += '<td>' + item.member_id + '</td>';
            statementsHtml += '<td>' + item.statement_id + '</td>';
            statementsHtml += '<td>' + item.statement_no + '</td>';
            statementsHtml += '</tr>';
        });
        $('#createdStatementsList').html(statementsHtml);
        $('#createdStatementsSection').show();
    }
}

// 初始化
$(function() {
    // 初始化时禁用确认按钮
    $('#confirmBtn').prop('disabled', true);
});
</script>
