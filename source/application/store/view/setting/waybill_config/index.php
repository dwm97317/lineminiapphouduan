<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">快递面单配置</div>
                </div>
                <div class="widget-body am-fr">
                    
                    <!-- 选项卡 -->
                    <div class="am-tabs" data-am-tabs>
                        <ul class="am-tabs-nav am-nav am-nav-tabs">
                            <li class="am-active"><a href="#tab-api">API 配置</a></li>
                            <li><a href="#tab-waybill">面单配置</a></li>
                        </ul>

                        <div class="am-tabs-bd">
                            <!-- API 配置选项卡 -->
                            <div class="am-tab-panel am-fade am-in am-active" id="tab-api">
                                <form id="api-config-form" class="am-form tpl-form-line-form">
                                    <fieldset>
                                        <legend>中通快递 API 配置</legend>
                                        
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">API 地址</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="am-form-field" id="zt-api-url" placeholder="https://api.zhongtong.com">
                                                <small class="am-text-grey">中通快递 API 接口地址</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">API Key</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="am-form-field" id="zt-api-key" placeholder="请输入中通 API Key">
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">API Secret</label>
                                            <div class="am-u-sm-9">
                                                <input type="password" class="am-form-field" id="zt-api-secret" placeholder="请输入中通 API Secret">
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">公司代码</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="am-form-field" id="zt-company-code" value="ZTO" readonly>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <fieldset>
                                        <legend>顺丰快递 API 配置 (丰桥)</legend>
                                        
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">API 地址</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="am-form-field" id="sf-api-url" placeholder="https://bspgw.sf-express.com/std/service">
                                                <small class="am-text-grey">生产环境: https://bspgw.sf-express.com/std/service <br> 沙箱环境: https://sfapi-sbox.sf-express.com/std/service</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">合作伙伴编码 (partnerID)</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="am-form-field" id="sf-api-key" placeholder="请输入顺丰分配的 Partner ID (顾客编码)">
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">校验码/密钥 (checkword)</label>
                                            <div class="am-u-sm-9">
                                                <input type="password" class="am-form-field" id="sf-api-secret" placeholder="请输入顺丰分配的 Checkword / 密钥">
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">月结卡号 (custid)</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="am-form-field" id="sf-custid" placeholder="请输入顺丰月结卡号 (可选)">
                                                <small class="am-text-grey">下单时需要使用的月结卡号，如果是寄付月结则必填</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">默认付款方式</label>
                                            <div class="am-u-sm-9">
                                                <select id="sf-pay-method" class="am-form-field">
                                                    <option value="1">寄方付 (Sender Pay)</option>
                                                    <option value="2">收方付 (Receiver Pay)</option>
                                                    <option value="3">第三方付 (Third Party Pay)</option>
                                                </select>
                                                <small class="am-text-grey">顺丰下单时的默认付款方式，寄方付通常需要配置月结卡号</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">公司代码</label>
                                            <div class="am-u-sm-9">
                                                <input type="text" class="am-form-field" id="sf-company-code" value="SF" readonly>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-push-3">
                                            <button type="button" id="save-api-config" class="am-btn am-btn-secondary">
                                                <i class="am-icon-save"></i> 保存 API 配置
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>

                            <!-- 面单配置选项卡 -->
                            <div class="am-tab-panel am-fade" id="tab-waybill">
                                
                                <!-- 快递公司选择 -->
                                <div class="am-form-group">
                                    <label class="am-u-sm-3 am-form-label">选择快递公司</label>
                                    <div class="am-u-sm-9">
                                        <select id="express-type" class="am-form-field">
                                            <option value="zhongtong">中通快递</option>
                                            <option value="shunfeng">顺丰快递</option>
                                        </select>
                                    </div>
                                </div>

                                <hr>

                                <!-- 配置表单 -->
                                <form id="waybill-config-form" class="am-form tpl-form-line-form">
                        
                                    <!-- 字段显示配置 -->
                                    <fieldset>
                                        <legend>面单字段显示设置</legend>
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">显示字段</label>
                                            <div class="am-u-sm-9" id="field-checkboxes">
                                                <!-- 动态加载 -->
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- 快递公司特定字段 -->
                                    <fieldset id="company-fields-section">
                                        <legend>快递公司特定字段</legend>
                                        <div id="company-fields">
                                            <!-- 动态加载 -->
                                        </div>
                                    </fieldset>

                                    <!-- 打印参数 -->
                                    <fieldset>
                                        <legend>打印参数设置</legend>
                                        
                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">纸张大小</label>
                                            <div class="am-u-sm-9">
                                                <select id="paper-size" class="am-form-field">
                                                    <option value="76x130">76mm x 130mm</option>
                                                </select>
                                                <small class="am-text-grey">一联快递单标准尺寸</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">打印方向</label>
                                            <div class="am-u-sm-9">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="orientation" value="portrait" checked> 纵向
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="orientation" value="landscape"> 横向
                                                </label>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">缩放比例</label>
                                            <div class="am-u-sm-9">
                                                <input type="number" id="scale" class="am-form-field" min="50" max="150" value="100">
                                                <small class="am-text-grey">范围: 50% - 150%</small>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <!-- 操作按钮 -->
                                    <div class="am-form-group">
                                        <div class="am-u-sm-9 am-u-sm-push-3">
                                            <button type="button" id="save-config" class="am-btn am-btn-secondary">
                                                <i class="am-icon-save"></i> 保存配置
                                            </button>
                                            <button type="button" id="reset-config" class="am-btn am-btn-default">
                                                <i class="am-icon-refresh"></i> 恢复默认
                                            </button>
                                        </div>
                                    </div>
                                </form>

                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    var currentExpressType = 'zhongtong';
    var currentConfig = {};
    var fieldDefinitions = {};
    var apiConfig = {};

    // ========== API 配置相关 ==========
    
    // 加载 API 配置
    function loadApiConfig() {
        $.get('<?= url("setting.waybill_config/getApiConfig") ?>', function(result) {
            console.log('API Config Result:', result);
            // 兼容性处理：后端可能把数据放在 msg 字段中，也可能放在 data 字段中
            var data = result.data;
            if (Array.isArray(data) && data.length === 0 && result.msg && typeof result.msg === 'object') {
                data = result.msg;
            }
            
            if (result.code === 1 && data) {
                apiConfig = data;
                applyApiConfig(apiConfig);
            }
        });
    }

    // 应用 API 配置到表单
    function applyApiConfig(config) {
        if (config.zhongtong) {
            $('#zt-api-url').val(config.zhongtong.api_url || '');
            $('#zt-api-key').val(config.zhongtong.api_key || '');
            $('#zt-api-secret').val(config.zhongtong.api_secret || '');
            $('#zt-company-code').val(config.zhongtong.company_code || 'ZTO');
        }
        
        if (config.shunfeng) {
            $('#sf-api-url').val(config.shunfeng.api_url || '');
            $('#sf-api-key').val(config.shunfeng.api_key || '');
            $('#sf-api-secret').val(config.shunfeng.api_secret || '');
            $('#sf-custid').val(config.shunfeng.custid || '');
            $('#sf-pay-method').val(config.shunfeng.pay_method || '1');
            $('#sf-company-code').val(config.shunfeng.company_code || 'SF');
        }
    }

    // 保存 API 配置
    $('#save-api-config').click(function() {
        var config = {
            zhongtong: {
                api_url: $('#zt-api-url').val(),
                api_key: $('#zt-api-key').val(),
                api_secret: $('#zt-api-secret').val(),
                company_code: $('#zt-company-code').val()
            },
            shunfeng: {
                api_url: $('#sf-api-url').val(),
                api_key: $('#sf-api-key').val(),
                api_secret: $('#sf-api-secret').val(),
                custid: $('#sf-custid').val(),
                pay_method: $('#sf-pay-method').val(),
                company_code: $('#sf-company-code').val()
            }
        };
        
        var loadIndex = layer.load(1); // 开启loading
        $.post('<?= url("setting.waybill_config/saveApiConfig") ?>', {
            config: JSON.stringify(config)
        }, function(result) {
            layer.close(loadIndex); // 关闭loading
            if (result.code === 1) {
                layer.msg(result.msg, {icon: 1, time: 2000});
            } else {
                layer.msg(result.msg, {icon: 2, time: 2000});
            }
        });
    });

    // ========== 面单配置相关 ==========
    
    // 加载配置
    function loadConfig(expressType) {
        currentExpressType = expressType;
        
        // 获取字段定义
        $.get('<?= url("setting.waybill_config/getFieldList") ?>', {
            express_type: expressType
        }, function(result) {
            if (result.code === 1 && result.data) {
                fieldDefinitions = result.data;
                renderFieldCheckboxes(fieldDefinitions.fields || []);
                renderCompanyFields(fieldDefinitions.company_fields || []);
            }
        });

        // 获取当前配置
        $.get('<?= url("setting.waybill_config/getConfig") ?>', {
            express_type: expressType
        }, function(result) {
            if (result.code === 1) {
                currentConfig = result.data;
                applyConfig(currentConfig);
            }
        });
    }

    // 渲染字段复选框
    function renderFieldCheckboxes(fields) {
        if (!fields || !Array.isArray(fields)) {
            console.error('fields is not an array', fields);
            return;
        }
        var html = '';
        fields.forEach(function(field) {
            var checked = currentConfig.fields && currentConfig.fields[field.key] ? 'checked' : '';
            var required = field.required ? '<span class="am-text-danger">*</span>' : '';
            var disabled = field.required ? 'disabled' : '';
            
            html += '<label class="am-checkbox-inline">';
            html += '<input type="checkbox" data-field="' + field.key + '" ' + checked + ' ' + disabled + '> ';
            html += field.label + required;
            html += '</label> ';
        });
        $('#field-checkboxes').html(html);
    }

    // 渲染快递公司特定字段
    function renderCompanyFields(fields) {
        if (!fields || fields.length === 0) {
            $('#company-fields-section').hide();
            return;
        }
        
        $('#company-fields-section').show();
        var html = '';
        
        fields.forEach(function(field) {
            var value = currentConfig.company_fields && currentConfig.company_fields[field.key] ? currentConfig.company_fields[field.key] : '';
            
            html += '<div class="am-form-group">';
            html += '<label class="am-u-sm-3 am-form-label">' + field.label + '</label>';
            html += '<div class="am-u-sm-9">';
            
            if (field.type === 'select') {
                html += '<select class="am-form-field" data-company-field="' + field.key + '">';
                for (var optValue in field.options) {
                    var selected = value == optValue ? 'selected' : '';
                    html += '<option value="' + optValue + '" ' + selected + '>' + field.options[optValue] + '</option>';
                }
                html += '</select>';
            } else {
                html += '<input type="text" class="am-form-field" data-company-field="' + field.key + '" value="' + value + '">';
            }
            
            html += '</div>';
            html += '</div>';
        });
        
        $('#company-fields').html(html);
    }

    // 应用配置到表单
    function applyConfig(config) {
        // 字段显示
        if (config.fields) {
            for (var key in config.fields) {
                $('input[data-field="' + key + '"]').prop('checked', config.fields[key]);
            }
        }

        // 公司字段
        if (config.company_fields) {
            for (var key in config.company_fields) {
                $('[data-company-field="' + key + '"]').val(config.company_fields[key]);
            }
        }

        // 打印参数
        if (config.print_params) {
            $('#paper-size').val(config.print_params.paper_size || '76x130');
            $('input[name="orientation"][value="' + (config.print_params.orientation || 'portrait') + '"]').prop('checked', true);
            $('#scale').val(config.print_params.scale || 100);
        }
    }

    // 收集表单数据
    function collectFormData() {
        var config = {
            fields: {},
            company_fields: {},
            print_params: {}
        };

        // 收集字段显示设置
        $('input[data-field]').each(function() {
            var key = $(this).data('field');
            config.fields[key] = $(this).is(':checked');
        });

        // 收集公司字段
        $('[data-company-field]').each(function() {
            var key = $(this).data('company-field');
            config.company_fields[key] = $(this).val();
        });

        // 收集打印参数
        config.print_params = {
            paper_size: $('#paper-size').val(),
            orientation: $('input[name="orientation"]:checked').val(),
            scale: parseInt($('#scale').val())
        };

        return config;
    }

    // 保存配置
    $('#save-config').click(function() {
        var config = collectFormData();
        
        var loadIndex = layer.load(1); // 开启loading
        $.post('<?= url("setting.waybill_config/saveConfig") ?>', {
            express_type: currentExpressType,
            config: JSON.stringify(config)
        }, function(result) {
            layer.close(loadIndex); // 关闭loading
            if (result.code === 1) {
                layer.msg(result.msg, {icon: 1, time: 2000});
            } else {
                layer.msg(result.msg, {icon: 2, time: 2000});
            }
        });
    });

    // 恢复默认配置
    $('#reset-config').click(function() {
        layer.confirm('确定要恢复默认配置吗？', {
            btn: ['确定', '取消']
        }, function(index) {
            $.post('<?= url("setting.waybill_config/resetConfig") ?>', {
                express_type: currentExpressType
            }, function(result) {
                if (result.code === 1) {
                    layer.msg(result.msg, {icon: 1});
                    currentConfig = result.data;
                    applyConfig(currentConfig);
                } else {
                    layer.msg(result.msg, {icon: 2});
                }
                layer.close(index);
            });
        });
    });

    // 快递公司切换
    $('#express-type').change(function() {
        loadConfig($(this).val());
    });

    // 初始加载
    loadApiConfig();
    loadConfig('zhongtong');
});
</script>
