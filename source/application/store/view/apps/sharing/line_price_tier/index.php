<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">线路价格阶梯配置</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-form-group">
                        <label class="am-u-sm-3 am-u-lg-2 am-form-label form-require">选择线路</label>
                        <div class="am-u-sm-9 am-u-end">
                            <select id="line-select" class="am-form-field">
                                <option value="">请选择线路</option>
                                <?php foreach ($lines as $line): ?>
                                <option value="<?= $line['id'] ?>"><?= $line['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div id="tier-container" style="display:none;">
                        <div class="am-form-group">
                            <label class="am-u-sm-3 am-u-lg-2 am-form-label">价格阶梯</label>
                            <div class="am-u-sm-9 am-u-end">
                                <table class="am-table am-table-bordered am-table-striped" id="tier-table">
                                    <thead>
                                        <tr>
                                            <th width="80">序号</th>
                                            <th width="150">最小重量(kg)</th>
                                            <th width="150">单价(฿/kg)</th>
                                            <th width="200">阶梯名称</th>
                                            <th width="100">操作</th>
                                        </tr>
                                    </thead>
                                    <tbody id="tier-tbody">
                                    </tbody>
                                </table>
                                <button type="button" class="am-btn am-btn-secondary am-btn-sm" id="add-tier-btn">
                                    <i class="am-icon-plus"></i> 添加阶梯
                                </button>
                            </div>
                        </div>
                        
                        <div class="am-form-group">
                            <div class="am-u-sm-9 am-u-sm-push-3 am-margin-top-lg">
                                <button type="button" class="am-btn am-btn-primary" id="save-btn">
                                    <i class="am-icon-save"></i> 保存配置
                                </button>
                                <a href="<?= url('apps.sharing.setting/basic') ?>" class="am-btn am-btn-default">
                                    <i class="am-icon-reply"></i> 返回设置
                                </a>
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
    let tierIndex = 0;
    
    // 选择线路时加载价格阶梯
    $('#line-select').on('change', function() {
        const lineId = $(this).val();
        if (!lineId) {
            $('#tier-container').hide();
            return;
        }
        
        loadTiers(lineId);
    });
    
    // 加载价格阶梯
    function loadTiers(lineId) {
        $.ajax({
            url: '<?= url('apps.sharing.line_price_tier/getTiers') ?>',
            type: 'GET',
            data: { line_id: lineId },
            dataType: 'json',
            success: function(res) {
                if (res.code === 1) {
                    $('#tier-tbody').empty();
                    tierIndex = 0;
                    
                    if (res.data.list && res.data.list.length > 0) {
                        res.data.list.forEach(function(tier) {
                            addTierRow(tier);
                        });
                    } else {
                        // 添加默认阶梯
                        addTierRow({ min_weight: 0, price_per_kg: 100, tier_name: '基础价' });
                    }
                    
                    $('#tier-container').show();
                } else {
                    layer.msg(res.msg || '加载失败');
                }
            }
        });
    }
    
    // 添加阶梯行
    function addTierRow(data) {
        data = data || {};
        tierIndex++;
        
        const row = `
            <tr data-index="${tierIndex}">
                <td>${tierIndex}</td>
                <td>
                    <input type="number" class="am-form-field" name="min_weight" 
                           value="${data.min_weight || 0}" min="0" step="0.01" required>
                </td>
                <td>
                    <input type="number" class="am-form-field" name="price_per_kg" 
                           value="${data.price_per_kg || 100}" min="0.01" step="0.01" required>
                </td>
                <td>
                    <input type="text" class="am-form-field" name="tier_name" 
                           value="${data.tier_name || ''}" placeholder="如：基础价、优惠价">
                </td>
                <td>
                    <button type="button" class="am-btn am-btn-danger am-btn-xs delete-tier-btn">
                        <i class="am-icon-trash"></i> 删除
                    </button>
                </td>
            </tr>
        `;
        
        $('#tier-tbody').append(row);
    }
    
    // 添加阶梯按钮
    $('#add-tier-btn').on('click', function() {
        addTierRow();
    });
    
    // 删除阶梯
    $(document).on('click', '.delete-tier-btn', function() {
        if ($('#tier-tbody tr').length <= 1) {
            layer.msg('至少保留一个价格阶梯');
            return;
        }
        $(this).closest('tr').remove();
        updateRowNumbers();
    });
    
    // 更新行号
    function updateRowNumbers() {
        $('#tier-tbody tr').each(function(index) {
            $(this).find('td:first').text(index + 1);
        });
    }
    
    // 保存配置
    $('#save-btn').on('click', function() {
        const lineId = $('#line-select').val();
        if (!lineId) {
            layer.msg('请选择线路');
            return;
        }
        
        const tiers = [];
        let hasError = false;
        
        $('#tier-tbody tr').each(function() {
            const $row = $(this);
            const minWeight = parseFloat($row.find('[name="min_weight"]').val());
            const pricePerKg = parseFloat($row.find('[name="price_per_kg"]').val());
            const tierName = $row.find('[name="tier_name"]').val();
            
            if (isNaN(minWeight) || isNaN(pricePerKg)) {
                hasError = true;
                return false;
            }
            
            if (minWeight < 0 || pricePerKg <= 0) {
                layer.msg('重量和价格必须为正数');
                hasError = true;
                return false;
            }
            
            tiers.push({
                min_weight: minWeight,
                price_per_kg: pricePerKg,
                tier_name: tierName
            });
        });
        
        if (hasError) {
            return;
        }
        
        if (tiers.length === 0) {
            layer.msg('请至少添加一个价格阶梯');
            return;
        }
        
        $.ajax({
            url: '<?= url('apps.sharing.line_price_tier/saveTiers') ?>',
            type: 'POST',
            data: {
                line_id: lineId,
                tiers: tiers
            },
            dataType: 'json',
            success: function(res) {
                if (res.code === 1) {
                    layer.msg(res.msg || '保存成功', {
                        icon: 1,
                        time: 1500
                    }, function() {
                        loadTiers(lineId);
                    });
                } else {
                    layer.msg(res.msg || '保存失败');
                }
            }
        });
    });
});
</script>
