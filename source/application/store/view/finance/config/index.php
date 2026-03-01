<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">财务配置管理</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- Tab导航 -->
                    <!-- Tab导航 -->
                    <div class="am-tabs" id="finance-tabs" data-am-tabs>
                        <ul class="am-tabs-nav am-nav am-nav-tabs">
                            <li class="am-active"><a href="#tab-template">账单模板</a></li>
                            <li><a href="#tab-global">全局默认单价</a></li>
                            <li><a href="#tab-customer">客户单价配置</a></li>
                            <li><a href="#tab-history">历史单价导入</a></li>
                            <li><a href="#tab-statement-list">账单列表</a></li>
                        </ul>

                        <!-- Tab内容 -->
                        <div class="am-tabs-bd">
                            <!-- 账单模板 -->
                            <div class="am-tab-panel am-fade am-in am-active" id="tab-template">
                                <div class="am-margin-top">
                                    <button type="button" class="am-btn am-btn-primary am-btn-sm" onclick="addTemplate()">
                                        <i class="am-icon-plus"></i> 新增模板
                                    </button>
                                </div>
                                <table class="am-table am-table-striped am-table-hover am-table-bordered am-margin-top">
                                    <thead>
                                        <tr>
                                            <th width="80">ID</th>
                                            <th>模板名称</th>
                                            <th>账单标题</th>
                                            <th width="100">默认模板</th>
                                            <th width="150">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="templateList"></tbody>
                                </table>
                            </div>

                            <!-- 全局默认单价 -->
                            <div class="am-tab-panel am-fade" id="tab-global">
                                <form class="am-form am-form-horizontal am-margin-top" id="globalConfigForm">
                                    <input type="hidden" name="member_id" value="">
                                    <div class="am-form-group">
                                        <label class="am-u-sm-3 am-form-label">计价方式</label>
                                        <div class="am-u-sm-9">
                                            <select name="price_type" id="global_price_type" class="am-form-field">
                                                <option value="1">固定单价</option>
                                                <option value="2">阶梯价格</option>
                                                <option value="5">自定义公式</option>
                                            </select>
                                        </div>
                                    </div>
                                    <!-- 固定单价配置 -->
                                    <div class="price-config" id="global-fixed-config">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">单价(元/KG)</label>
                                            <div class="am-u-sm-9">
                                                <input type="number" name="unit_price" class="am-form-field" step="0.01" min="0" placeholder="如：46.00">
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 阶梯价格配置 -->
                                    <div class="price-config" id="global-tier-config" style="display:none;">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">阶梯配置</label>
                                            <div class="am-u-sm-9">
                                                <table class="am-table am-table-bordered" id="global-tier-table">
                                                    <thead>
                                                        <tr>
                                                            <th>最小重量(KG)</th>
                                                            <th>最大重量(KG)</th>
                                                            <th>单价(元/KG)</th>
                                                            <th width="80">操作</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                                <button type="button" class="am-btn am-btn-success am-btn-xs" onclick="addTier('global')">
                                                    <i class="am-icon-plus"></i> 添加阶梯
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <!-- 自定义公式配置 -->
                                    <div class="price-config" id="global-formula-config" style="display:none;">
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">计价公式</label>
                                            <div class="am-u-sm-9">
                                                <textarea name="price_formula" class="am-form-field" rows="3" placeholder="如：{weight} * 46 + 10"></textarea>
                                                <small class="am-text-muted">可用变量：{weight}重量</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-offset-3">
                                            <button type="submit" class="am-btn am-btn-primary">保存配置</button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- 客户单价配置 -->
                            <div class="am-tab-panel am-fade" id="tab-customer">
                                <div class="am-margin-top">
                                    <button type="button" class="am-btn am-btn-primary am-btn-sm" onclick="addCustomerConfig()">
                                        <i class="am-icon-plus"></i> 新增客户配置
                                    </button>
                                </div>
                                <table class="am-table am-table-striped am-table-hover am-table-bordered am-margin-top">
                                    <thead>
                                        <tr>
                                            <th width="80">ID</th>
                                            <th>客户</th>
                                            <th>计价方式</th>
                                            <th>单价配置</th>
                                            <th width="100">状态</th>
                                            <th width="150">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="customerConfigList"></tbody>
                                </table>
                            </div>

                            <!-- 历史单价导入 -->
                            <div class="am-tab-panel am-fade" id="tab-history">
                                <div class="am-panel am-panel-default am-margin-top">
                                    <div class="am-panel-hd">导入历史单价</div>
                                    <div class="am-panel-bd">
                                        <form class="am-form" id="importForm">
                                            <div class="am-form-group">
                                                <label>选择文件</label>
                                                <input type="file" name="file" id="importFile" accept=".txt,.xls,.xlsx">
                                                <small class="am-text-muted">支持TXT和Excel格式</small>
                                            </div>
                                            <div class="am-form-group">
                                                <button type="button" class="am-btn am-btn-primary" onclick="importHistoryPrice()">
                                                    <i class="am-icon-upload"></i> 开始导入
                                                </button>
                                            </div>
                                        </form>
                                        <div class="am-alert am-alert-secondary">
                                            <h4>TXT格式说明：</h4>
                                            <p>每行一条记录，格式：客户ID 单价</p>
                                            <p>示例：</p>
                                            <pre>31398 48.00
31966 50.00
# 这是注释行</pre>
                                        </div>
                                        <div class="am-alert am-alert-secondary">
                                            <h4>Excel格式说明：</h4>
                                            <p>第一列：客户ID，第二列：单价</p>
                                            <p>只读取第一个Sheet，不需要表头</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- 账单列表 -->
                            <div class="am-tab-panel am-fade" id="tab-statement-list">
                                <!-- 筛选工具栏 -->
                                <div class="page_toolbar am-margin-bottom-xs am-cf">
                                    <form class="toolbar-form" id="statementSearchForm">
                                        <div class="am-u-sm-12 am-u-md-12">
                                            <div class="am">
                                                <!-- 客户选择 -->
                                                <div class="am-form-group am-fl">
                                                    <select name="member_id" id="statement_member_id" data-am-selected="{btnSize: 'sm', placeholder: '选择客户', searchBox: 1}">
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
                                                    <input type="text" name="start_date" class="am-form-field statement-date" placeholder="开始日期" readonly>
                                                </div>
                                                <div class="am-form-group am-fl">
                                                    <input type="text" name="end_date" class="am-form-field statement-date" placeholder="结束日期" readonly>
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
                                <div class="am-margin-bottom-sm" id="statement_statistics">
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
                                            <th>用户ID</th>
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
                                        <tbody id="statementListBody">
                                        <!-- 动态填充 -->
                                        </tbody>
                                    </table>
                                </div>

                                <!-- 分页 -->
                                <div class="am-u-lg-12 am-cf">
                                    <div class="am-fr" id="statement_pagination"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 模板编辑弹窗 -->
<div class="am-modal am-modal-no-btn" tabindex="-1" id="templateModal">
    <div class="am-modal-dialog">
        <div class="am-modal-hd">编辑账单模板
            <a href="javascript:;" class="am-close am-close-spin" data-am-modal-close>&times;</a>
        </div>
        <div class="am-modal-bd">
            <form class="am-form" id="templateForm">
                <input type="hidden" name="id" id="template_id">
                
                <div class="am-form-group">
                    <label>模板名称 <span class="am-text-danger">*</span></label>
                    <input type="text" name="template_name" class="am-form-field" required>
                </div>
                
                <div class="am-form-group">
                    <label>账单标题 <span class="am-text-danger">*</span></label>
                    <input type="text" name="title" class="am-form-field" placeholder="如：集运订单对账单" required>
                </div>
                
                <div class="am-form-group">
                    <label>账单LOGO (可选)</label>
                    <div class="am-form-file">
                        <button type="button" class="am-btn am-btn-default am-btn-sm"><i class="am-icon-upload"></i> 选择LOGO图片</button>
                        <input type="file" class="template-img-upload" data-type="logo" accept="image/png,image/jpeg,image/jpg">
                    </div>
                    <div>
                        <img id="preview_logo_path" src="" style="max-height: 80px; margin-top: 10px; display: none;">
                    </div>
                    <input type="hidden" name="logo_path" id="input_logo_path">
                </div>

                <div class="am-form-group">
                    <label>支付宝收款码 (可选)</label>
                    <div class="am-form-file">
                        <button type="button" class="am-btn am-btn-default am-btn-sm"><i class="am-icon-upload"></i> 选择收款码</button>
                        <input type="file" class="template-img-upload" data-type="qrcode_alipay" accept="image/png,image/jpeg,image/jpg">
                    </div>
                    <div>
                        <img id="preview_alipay_qr_path" src="" style="max-height: 80px; margin-top: 10px; display: none;">
                    </div>
                    <input type="hidden" name="alipay_qr_path" id="input_alipay_qr_path">
                </div>

                <div class="am-form-group">
                    <label>微信收款码 (可选)</label>
                    <div class="am-form-file">
                        <button type="button" class="am-btn am-btn-default am-btn-sm"><i class="am-icon-upload"></i> 选择收款码</button>
                        <input type="file" class="template-img-upload" data-type="qrcode_wechat" accept="image/png,image/jpeg,image/jpg">
                    </div>
                    <div>
                        <img id="preview_wechat_qr_path" src="" style="max-height: 80px; margin-top: 10px; display: none;">
                    </div>
                    <input type="hidden" name="wechat_qr_path" id="input_wechat_qr_path">
                </div>
                
                <div class="am-form-group">
                    <label>温馨提示</label>
                    <textarea name="notice_text" class="am-form-field" rows="3" placeholder="请输入账单底部的提示文字"></textarea>
                </div>
                
                <div class="am-form-group">
                    <label>
                        <input type="checkbox" name="is_default" value="1"> 设为默认模板
                    </label>
                </div>
                
                <div class="am-form-group">
                    <button type="submit" class="am-btn am-btn-primary am-btn-block">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- 客户配置编辑弹窗 -->
<div class="am-modal am-modal-no-btn" tabindex="-1" id="customerConfigModal">
    <div class="am-modal-dialog">
        <div class="am-modal-hd">编辑客户单价配置
            <a href="javascript:;" class="am-close am-close-spin" data-am-modal-close>&times;</a>
        </div>
        <div class="am-modal-bd">
            <form class="am-form" id="customerConfigForm">
                <input type="hidden" name="id" id="customer_config_id">
                
                <div class="am-form-group">
                    <label>选择客户 <span class="am-text-danger">*</span></label>
                    <select name="member_id" id="customer_member_id" class="am-form-field" data-am-selected="{searchBox: 1, btnSize: 'sm', dropUp: 0, maxHeight: 300, btnWidth: '100%'}" required>
                        <option value="">请选择用户(支持ID检索)</option>
                    </select>
                </div>
                
                <div class="am-form-group">
                    <label>计价方式 <span class="am-text-danger">*</span></label>
                    <select name="price_type" id="customer_price_type" class="am-form-field" required>
                        <option value="1">固定单价</option>
                        <option value="2">阶梯价格</option>
                        <option value="5">自定义公式</option>
                    </select>
                </div>

                <!-- 固定单价 -->
                <div class="price-config" id="customer-fixed-config">
                    <div class="am-form-group">
                        <label>单价(元/KG)</label>
                        <input type="number" name="unit_price" class="am-form-field" step="0.01" min="0">
                    </div>
                </div>

                <!-- 阶梯价格 -->
                <div class="price-config" id="customer-tier-config" style="display:none;">
                    <div class="am-form-group">
                        <label>阶梯配置</label>
                        <table class="am-table am-table-bordered" id="customer-tier-table">
                            <thead>
                            <tr>
                                <th>最小重量</th>
                                <th>最大重量</th>
                                <th>单价</th>
                                <th width="60">操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <!-- 动态添加 -->
                            </tbody>
                        </table>
                        <button type="button" class="am-btn am-btn-success am-btn-xs" onclick="addTier('customer')">
                            <i class="am-icon-plus"></i> 添加阶梯
                        </button>
                    </div>
                </div>

                <!-- 自定义公式 -->
                <div class="price-config" id="customer-formula-config" style="display:none;">
                    <div class="am-form-group">
                        <label>计价公式</label>
                        <textarea name="price_formula" class="am-form-field" rows="3"></textarea>
                    </div>
                </div>

                <div class="am-form-group">
                    <label>
                        <input type="checkbox" name="status" value="1" checked> 启用配置
                    </label>
                </div>
                
                <div class="am-form-group">
                    <button type="submit" class="am-btn am-btn-primary am-btn-block">保存</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(function() {
    // 修复AmazeUI由于页面特定嵌套产生的Modal层级遮罩问题
    $('#templateModal').appendTo('body');
    $('#customerConfigModal').appendTo('body');

    // 处理URL Hash，初始化激活对应的Tab
    var hash = window.location.hash;
    if (hash) {
        var $tab = $('.am-nav-tabs a[href="' + hash + '"]');
        if ($tab.length) {
            $tab.trigger('click');
        }
    }
    
    // 初始化
    loadTemplateList();
    loadGlobalConfig();
    loadCustomerConfigList();
    loadMemberList();
    
    // Tab切换事件监听 - 延迟加载账单列表
    $('#finance-tabs').on('opened.tabs.amui', function(e) {
        var $target = $(e.target);
        if ($target.attr('href') === '#tab-statement-list') {
            // 首次打开账单列表Tab时初始化
            if (!window.statementListInitialized) {
                initStatementList();
                window.statementListInitialized = true;
            }
        }
    });
    
    // 全局配置计价方式切换
    $('#global_price_type').on('change', function() {
        switchPriceConfig('global', $(this).val());
    });
    
    // 客户配置计价方式切换
    $('#customer_price_type').on('change', function() {
        switchPriceConfig('customer', $(this).val());
    });
    
    // 全局配置表单提交
    $('#globalConfigForm').on('submit', function(e) {
        e.preventDefault();
        saveGlobalConfig();
    });
    
    // 模板表单提交
    $('#templateForm').on('submit', function(e) {
        e.preventDefault();
        saveTemplate();
    });
    
    // 客户配置表单提交
    $('#customerConfigForm').on('submit', function(e) {
        e.preventDefault();
        saveCustomerConfig();
    });
    
    // 监听模板文件上传
    $('.template-img-upload').on('change', function() {
        var file = this.files[0];
        if (!file) return;
        
        var type = $(this).data('type');
        var inputId = type === 'logo' ? 'input_logo_path' : (type === 'qrcode_alipay' ? 'input_alipay_qr_path' : 'input_wechat_qr_path');
        var previewId = type === 'logo' ? 'preview_logo_path' : (type === 'qrcode_alipay' ? 'preview_alipay_qr_path' : 'preview_wechat_qr_path');
        
        // 获取当前编辑的模板ID（如果有）
        var templateId = $('#template_id').val() || '';
        
        var formData = new FormData();
        formData.append('file', file);
        formData.append('type', type);
        formData.append('template_id', templateId);
        
        var loading = layer.load(1, {shade: [0.1, '#fff']});
        $.ajax({
            url: '<?= url("finance.config/upload") ?>',
            type: 'POST',
            data: formData,
            contentType: false,
            processData: false,
            success: function(res) {
                layer.close(loading);
                if (res.code == 1) {
                    $('#' + inputId).val(res.data.file_path);
                    // 添加时间戳避免浏览器缓存
                    var timestamp = new Date().getTime();
                    $('#' + previewId).attr('src', "<?= rtrim(base_url(), '/') ?>/" + res.data.file_path.replace('./', '') + '?t=' + timestamp).show();
                    layer.msg('上传成功');
                } else {
                    layer.msg(res.msg);
                }
            },
            error: function() {
                layer.close(loading);
                layer.msg('网络错误或支持的文件过大');
            }
        });
        
        // 清空input，允许重复选择同一个文件
        $(this).val('');
    });
});

// 切换计价配置显示
function switchPriceConfig(prefix, priceType) {
    $('.price-config').hide();
    
    switch(priceType) {
        case '1':
            $('#' + prefix + '-fixed-config').show();
            break;
        case '2':
            $('#' + prefix + '-tier-config').show();
            break;
        case '5':
            $('#' + prefix + '-formula-config').show();
            break;
    }
}

// 加载模板列表
function loadTemplateList() {
    $.ajax({
        url: '<?= url("finance.config/templateList") ?>',
        success: function(res) {
            if (res.code == 1) {
                renderTemplateList(res.data.list);
            }
        }
    });
}

// 渲染模板列表
function renderTemplateList(list) {
    var html = '';
    
    if (list.length == 0) {
        html = '<tr><td colspan="5" class="am-text-center">暂无数据</td></tr>';
    } else {
        $.each(list, function(i, item) {
            html += '<tr>';
            html += '<td>' + item.id + '</td>';
            html += '<td>' + item.template_name + '</td>';
            html += '<td>' + item.title + '</td>';
            html += '<td>';
            if (item.is_default == 1) {
                html += '<span class="am-badge am-badge-success">是</span>';
            } else {
                html += '<span class="am-badge">否</span>';
            }
            html += '</td>';
            html += '<td>';
            html += '<a href="javascript:;" class="am-btn am-btn-default am-btn-xs" onclick="editTemplate(' + item.id + ')">编辑</a> ';
            if (item.is_default != 1) {
                html += '<a href="javascript:;" class="am-btn am-btn-danger am-btn-xs" onclick="deleteTemplate(' + item.id + ')">删除</a>';
            }
            html += '</td>';
            html += '</tr>';
        });
    }
    
    $('#templateList').html(html);
}

// 新增模板
function addTemplate() {
    $('#templateForm')[0].reset();
    $('#template_id').val('');
    $('#input_logo_path').val('');
    $('#input_alipay_qr_path').val('');
    $('#input_wechat_qr_path').val('');
    $('#preview_logo_path').hide().removeAttr('src');
    $('#preview_alipay_qr_path').hide().removeAttr('src');
    $('#preview_wechat_qr_path').hide().removeAttr('src');
    $('#templateModal').modal('open');
}

// 编辑模板
function editTemplate(id) {
    $.ajax({
        url: '<?= url("finance.config/getTemplate") ?>',
        data: { id: id },
        success: function(res) {
            if (res.code == 1) {
                var data = res.data;
                $('#templateForm')[0].reset();
                $('#template_id').val(data.id);
                $('input[name="template_name"]').val(data.template_name);
                $('input[name="title"]').val(data.title);
                $('textarea[name="notice_text"]').val(data.notice_text);
                $('input[name="is_default"]').prop('checked', data.is_default == 1);
                
                $('#input_logo_path').val(data.logo_path || '');
                if (data.logo_path) {
                    var timestamp = new Date().getTime();
                    $('#preview_logo_path').attr('src', "<?= rtrim(base_url(), '/') ?>/" + data.logo_path.replace('./', '') + '?t=' + timestamp).show();
                } else {
                    $('#preview_logo_path').hide().removeAttr('src');
                }
                
                $('#input_alipay_qr_path').val(data.alipay_qr_path || '');
                if (data.alipay_qr_path) {
                    var timestamp = new Date().getTime();
                    $('#preview_alipay_qr_path').attr('src', "<?= rtrim(base_url(), '/') ?>/" + data.alipay_qr_path.replace('./', '') + '?t=' + timestamp).show();
                } else {
                    $('#preview_alipay_qr_path').hide().removeAttr('src');
                }
                
                $('#input_wechat_qr_path').val(data.wechat_qr_path || '');
                if (data.wechat_qr_path) {
                    var timestamp = new Date().getTime();
                    $('#preview_wechat_qr_path').attr('src', "<?= rtrim(base_url(), '/') ?>/" + data.wechat_qr_path.replace('./', '') + '?t=' + timestamp).show();
                } else {
                    $('#preview_wechat_qr_path').hide().removeAttr('src');
                }
                
                $('#templateModal').modal('open');
            } else {
                layer.msg(res.msg);
            }
        }
    });
}

// 删除模板
function deleteTemplate(id) {
    layer.confirm('确定要删除此模板吗？', function(index) {
        $.ajax({
            url: '<?= url("finance.config/deleteTemplate") ?>',
            type: 'POST',
            data: { id: id },
            success: function(res) {
                if (res.code == 1) {
                    layer.msg('删除成功');
                    layer.close(index);
                    loadTemplateList();
                } else {
                    layer.msg(res.msg);
                }
            }
        });
    });
}

// 保存模板
function saveTemplate() {
    $.ajax({
        url: '<?= url("finance.config/saveTemplate") ?>',
        type: 'POST',
        data: $('#templateForm').serialize(),
        success: function(res) {
            if (res.code == 1) {
                layer.msg('保存成功');
                $('#templateModal').modal('close');
                loadTemplateList();
            } else {
                layer.msg(res.msg);
            }
        }
    });
}

// 加载全局配置
function loadGlobalConfig() {
    $.ajax({
        url: '<?= url("finance.config/get") ?>',
        data: { member_id: '' },
        success: function(res) {
            if (res.code == 1 && res.data) {
                fillGlobalConfig(res.data);
            }
        }
    });
}

// 填充全局配置
function fillGlobalConfig(config) {
    if (!config) return;
    
    $('#global_price_type').val(config.price_type);
    switchPriceConfig('global', config.price_type.toString());
    
    if (config.price_type == 1) { // 固定单价
        $('#globalConfigForm input[name="unit_price"]').val(config.unit_price);
    } else if (config.price_type == 2) { // 阶梯价格
        var html = '';
        if (config.price_tier_json && config.price_tier_json.tiers) {
            $.each(config.price_tier_json.tiers, function(i, tier) {
                html += '<tr>' +
                    '<td><input type="number" name="tiers_min[]" class="am-form-field" step="0.01" value="' + tier.min + '" required></td>' +
                    '<td><input type="number" name="tiers_max[]" class="am-form-field" step="0.01" value="' + (tier.max || '') + '"></td>' +
                    '<td><input type="number" name="tiers_price[]" class="am-form-field" step="0.01" value="' + tier.price + '" required></td>' +
                    '<td><button type="button" class="am-btn am-btn-danger am-btn-xs" onclick="removeTier(this)">删除</button></td>' +
                    '</tr>';
            });
        }
        $('#global-tier-table tbody').html(html);
    } else if (config.price_type == 5) { // 自定义公式
        $('#globalConfigForm textarea[name="price_formula"]').val(config.price_formula);
    }
}

// 保存配置（通用，处理阶梯数据）
function saveConfig(formId, successCallback) {
    var $form = $('#' + formId);
    var formData = $form.serializeArray();
    var priceType = $form.find('[name="price_type"]').val();
    
    // 如果是阶梯配置，封装成JSON
    if (priceType == '2') {
        var tiers = [];
        var mins = $form.find('[name="tiers_min[]"]').map(function() { return $(this).val(); }).get();
        var maxs = $form.find('[name="tiers_max[]"]').map(function() { return $(this).val(); }).get();
        var prices = $form.find('[name="tiers_price[]"]').map(function() { return $(this).val(); }).get();
        
        for (var i = 0; i < mins.length; i++) {
            tiers.push({
                min: parseFloat(mins[i]),
                max: maxs[i] ? parseFloat(maxs[i]) : null,
                price: parseFloat(prices[i])
            });
        }
        formData.push({ name: 'price_tier_json[tiers]', value: JSON.stringify(tiers) });
        // 注意：后端可能期望的是数组结构，如果是 PHP 接收，直接传对象或JSON串
        // 这里的 service 期望的是 array，所以我们可能要在后端处理，或者前端传结构化的 name
    }
    
    $.ajax({
        url: '<?= url("finance.config/save") ?>',
        type: 'POST',
        data: $form.serialize(), // 默认 serialize
        success: function(res) {
            if (res.code == 1) {
                layer.msg('保存成功');
                if (successCallback) successCallback(res);
            } else {
                layer.msg(res.msg);
            }
        }
    });
}

// 后端支持多级参数接收，如 price_tier_json[tiers][0][min]
// 但是 JS 的 serialize 不支持这种自动转换，我们需要手动构建
function getPriceFormData(formId) {
    var $form = $('#' + formId);
    var data = {};
    var array = $form.serializeArray();
    $.each(array, function() {
        if (this.name.indexOf('[]') !== -1) {
             // 忽略这些，我们手动处理
        } else {
            data[this.name] = this.value;
        }
    });

    if (data.price_type == '2') {
        var tiers = [];
        $form.find('#' + formId.replace('ConfigForm', '') + '-tier-table tbody tr').each(function() {
            var $tr = $(this);
            tiers.push({
                min: $tr.find('[name="tiers_min[]"]').val(),
                max: $tr.find('[name="tiers_max[]"]').val(),
                price: $tr.find('[name="tiers_price[]"]').val()
            });
        });
        data.price_tier_json = { tiers: tiers };
    }
    return data;
}

// 保存全局配置
function saveGlobalConfig() {
    var data = getPriceFormData('globalConfigForm');
    $.ajax({
        url: '<?= url("finance.config/save") ?>',
        type: 'POST',
        data: data,
        success: function(res) {
            if (res.code == 1) {
                layer.msg('保存成功');
            } else {
                layer.msg(res.msg);
            }
        }
    });
}

// 加载客户配置列表
function loadCustomerConfigList() {
    $.ajax({
        url: '<?= url("finance.config/list") ?>',
        success: function(res) {
            if (res.code == 1) {
                renderCustomerConfigList(res.data.list);
            }
        }
    });
}

// 渲染客户配置列表
function renderCustomerConfigList(list) {
    var html = '';
    var priceTypes = {1: '固定单价', 2: '阶梯价格', 5: '自定义公式'};
    
    if (list.length == 0) {
        html = '<tr><td colspan="6" class="am-text-center">暂无数据</td></tr>';
    } else {
        $.each(list, function(i, item) {
            if (item.member_id) { // 只显示客户配置
                html += '<tr>';
                html += '<td>' + item.id + '</td>';
                html += '<td>' + (item.member_name || item.member_id) + '</td>';
                html += '<td>' + priceTypes[item.price_type] + '</td>';
                html += '<td>';
                if (item.price_type == 1) {
                    html += item.unit_price + ' 元/KG';
                } else {
                    html += '查看详情';
                }
                html += '</td>';
                html += '<td>';
                if (item.status == 1) {
                    html += '<span class="am-badge am-badge-success">启用</span>';
                } else {
                    html += '<span class="am-badge">禁用</span>';
                }
                html += '</td>';
                html += '<td>';
                html += '<a href="javascript:;" class="am-btn am-btn-default am-btn-xs" onclick="editCustomerConfig(' + item.id + ')">编辑</a> ';
                html += '<a href="javascript:;" class="am-btn am-btn-danger am-btn-xs" onclick="deleteCustomerConfig(' + item.id + ')">删除</a>';
                html += '</td>';
                html += '</tr>';
            }
        });
    }
    
    $('#customerConfigList').html(html);
}

// 加载客户列表
function loadMemberList() {
    $.ajax({
        url: '<?= url("user.user/getMemberList") ?>',
        success: function(res) {
            if (res.code == 1 && res.data.list) {
                var html = '<option value="">请选择客户 (支持ID/昵称检索)</option>';
                $.each(res.data.list, function(i, item) {
                    html += '<option value="' + item.user_id + '">ID:' + item.user_id + ' - ' + item.nickName + '</option>';
                });
                var $select = $('#customer_member_id');
                $select.html(html);
                if ($.fn.selected) {
                    $select.trigger('changed.selected.amui');
                }
            }
        }
    });
}

// 新增客户配置
function addCustomerConfig() {
    $('#customerConfigForm')[0].reset();
    $('#customer_config_id').val('');
    $('#customer_member_id').val('').trigger('changed.selected.amui');
    $('#customerConfigModal').modal('open');
}

// 编辑客户配置
function editCustomerConfig(id) {
    $.ajax({
        url: '<?= url("finance.config/list") ?>',
        success: function(res) {
            if (res.code == 1) {
                var config = res.data.list.find(function(item) { return item.id == id; });
                if (config) {
                    $('#customerConfigForm')[0].reset();
                    $('#customer_config_id').val(config.id);
                    $('#customer_member_id').val(config.member_id).trigger('changed.selected.amui');
                    $('#customer_price_type').val(config.price_type);
                    switchPriceConfig('customer', config.price_type.toString());
                    
                    if (config.price_type == 1) {
                        $('#customerConfigForm input[name="unit_price"]').val(config.unit_price);
                    } else if (config.price_type == 2) {
                        var html = '';
                        if (config.price_tier_json && config.price_tier_json.tiers) {
                            $.each(config.price_tier_json.tiers, function(i, tier) {
                                html += '<tr>' +
                                    '<td><input type="number" name="tiers_min[]" class="am-form-field" step="0.01" value="' + tier.min + '" required></td>' +
                                    '<td><input type="number" name="tiers_max[]" class="am-form-field" step="0.01" value="' + (tier.max || '') + '"></td>' +
                                    '<td><input type="number" name="tiers_price[]" class="am-form-field" step="0.01" value="' + tier.price + '" required></td>' +
                                    '<td><button type="button" class="am-btn am-btn-danger am-btn-xs" onclick="removeTier(this)">删除</button></td>' +
                                    '</tr>';
                            });
                        }
                        $('#customer-tier-table tbody').html(html);
                    } else if (config.price_type == 5) {
                        $('#customerConfigForm textarea[name="price_formula"]').val(config.price_formula);
                    }
                    
                    $('#customerConfigModal').modal('open');
                }
            }
        }
    });
}

// 保存客户配置
function saveCustomerConfig() {
    var data = getPriceFormData('customerConfigForm');
    $.ajax({
        url: '<?= url("finance.config/save") ?>',
        type: 'POST',
        data: data,
        success: function(res) {
            if (res.code == 1) {
                layer.msg('保存成功');
                $('#customerConfigModal').modal('close');
                loadCustomerConfigList();
            } else {
                layer.msg(res.msg);
            }
        }
    });
}

// 删除客户配置
function deleteCustomerConfig(id) {
    layer.confirm('确定要删除此配置吗？', function(index) {
        $.ajax({
            url: '<?= url("finance.config/delete") ?>',
            type: 'POST',
            data: { config_id: id },
            success: function(res) {
                if (res.code == 1) {
                    layer.msg('删除成功');
                    layer.close(index);
                    loadCustomerConfigList();
                } else {
                    layer.msg(res.msg);
                }
            }
        });
    });
}

// 添加阶梯
function addTier(prefix) {
    var html = '<tr>' +
        '<td><input type="number" name="tiers_min[]" class="am-form-field" step="0.01" required></td>' +
        '<td><input type="number" name="tiers_max[]" class="am-form-field" step="0.01"></td>' +
        '<td><input type="number" name="tiers_price[]" class="am-form-field" step="0.01" required></td>' +
        '<td><button type="button" class="am-btn am-btn-danger am-btn-xs" onclick="removeTier(this)">删除</button></td>' +
        '</tr>';
    $('#' + prefix + '-tier-table tbody').append(html);
}

// 删除阶梯
function removeTier(btn) {
    $(btn).closest('tr').remove();
}

// 导入历史单价
function importHistoryPrice() {
    var fileInput = document.getElementById('importFile');
    if (!fileInput.files.length) {
        layer.msg('请选择文件');
        return;
    }
    
    var formData = new FormData();
    formData.append('file', fileInput.files[0]);
    
    $.ajax({
        url: '<?= url("finance.config/importHistoryPrice") ?>',
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        success: function(res) {
            if (res.code == 1) {
                layer.msg(res.msg);
                fileInput.value = '';
            } else {
                layer.msg(res.msg);
            }
        }
    });
}
</script>

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
        console.log('Loading statement statistics, params:', params);
        
        $.ajax({
            url: '<?= url("package.statement/statistics") ?>',
            data: params,
            success: function(res) {
                console.log('Statement statistics response:', res);
                if (res.code == 1) {
                    $('#stat_total').text(res.data.total_count || 0);
                    $('#stat_paid').text(res.data.paid_count || 0);
                    $('#stat_unpaid').text(res.data.unpaid_count || 0);
                    $('#stat_void').text(res.data.void_count || 0);
                    $('#stat_amount').text((res.data.total_amount || 0).toFixed(2));
                } else {
                    console.error('Failed to load statistics:', res.msg);
                }
            },
            error: function(xhr, status, error) {
                console.error('Statistics AJAX error:', status, error);
            }
        });
    }
    
    // 加载账单列表
    function loadStatementList(page) {
        currentPage = page;
        var params = $('#statementSearchForm').serialize() + '&page=' + page;
        
        console.log('Loading statement list, params:', params);
        
        $.ajax({
            url: '<?= url("package.statement/getList") ?>',
            data: params,
            success: function(res) {
                console.log('Statement list response:', res);
                console.log('res.data:', res.data);
                console.log('res.data.list:', res.data.list);
                
                if (res.code == 1) {
                    // 处理返回的数据结构
                    var list = res.data.list || [];
                    var total = res.data.total || 0;
                    var pageSize = res.data.page_size || 20;
                    
                    console.log('Rendering list:', list, 'total:', total);
                    
                    renderStatementList(list);
                    renderStatementPagination(total, page, pageSize);
                } else {
                    console.error('Failed to load statement list:', res.msg);
                    layer.msg(res.msg || '加载失败');
                }
            },
            error: function(xhr, status, error) {
                console.error('AJAX error:', status, error);
                layer.msg('网络错误，请稍后重试');
            }
        });
    }
    
    // 渲染账单列表
    function renderStatementList(list) {
        var html = '';
        
        if (list.length == 0) {
            html = '<tr><td colspan="10" class="am-text-center">暂无数据</td></tr>';
        } else {
            $.each(list, function(i, item) {
                html += '<tr>';
                html += '<td>' + item.statement_no + '</td>';
                html += '<td>' + item.member_id + '</td>';
                html += '<td>' + (item.member_name || '-') + '</td>';
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
