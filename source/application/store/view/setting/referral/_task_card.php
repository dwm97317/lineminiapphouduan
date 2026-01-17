<?php
/**
 * 任务卡片组件
 * @param array $task 任务配置数据
 * @param string $role_type 角色类型：referrer/referee
 */
function renderTaskCard($task, $role_type) {
    // 解析 task_params
    $taskParams = [];
    if (!empty($task['task_params'])) {
        if (is_string($task['task_params'])) {
            $taskParams = json_decode($task['task_params'], true) ?: [];
        } elseif (is_array($task['task_params'])) {
            $taskParams = $task['task_params'];
        }
    }
    
    // 任务类型映射
    $taskTypeMap = [
        'invite_success' => '成功邀请用户',
        'first_recharge' => '首次充值',
        'first_order' => '首次下单',
        'complete_profile' => '完善资料'
    ];
    $taskTypeName = $taskTypeMap[$task['task_type']] ?? $task['task_type'];
    ?>
    
    <div class="am-panel am-panel-default task-card" style="margin-bottom: 15px; border-radius: 8px; overflow: hidden;">
        <div class="am-panel-hd" style="background: #f5f5f5; padding: 15px; border-bottom: 1px solid #e5e5e5;">
            <span class="task-name" style="font-size: 16px; font-weight: bold; color: #333;">
                <?= $task['config_name'] ?>
            </span>
            <span class="task-status" style="float: right;">
                <?php if ($task['is_enabled']): ?>
                    <span class="am-badge am-badge-success" style="border-radius: 12px;">已启用</span>
                <?php else: ?>
                    <span class="am-badge am-badge-secondary" style="border-radius: 12px;">已禁用</span>
                <?php endif; ?>
                
                <?php if ($task['is_required']): ?>
                    <span class="am-badge am-badge-danger" style="border-radius: 12px; margin-left: 5px;">必须完成</span>
                <?php endif; ?>
            </span>
        </div>
        
        <div class="am-panel-bd" style="padding: 15px;">
            <div class="task-type" style="color: #999; font-size: 14px; margin-bottom: 15px;">
                <strong>任务类型:</strong> <?= $taskTypeName ?>
            </div>
            
            <div class="am-form-group">
                <label class="am-checkbox-inline">
                    <input type="checkbox" 
                           name="task_config[<?= $role_type ?>][<?= $task['id'] ?>][is_enabled]" 
                           value="1" 
                           <?= $task['is_enabled'] ? 'checked' : '' ?> 
                           data-am-ucheck> 启用
                </label>
                <label class="am-checkbox-inline">
                    <input type="checkbox" 
                           name="task_config[<?= $role_type ?>][<?= $task['id'] ?>][is_required]" 
                           value="1" 
                           <?= $task['is_required'] ? 'checked' : '' ?> 
                           data-am-ucheck> 必须完成
                </label>
            </div>
            
            <?php if (!empty($taskParams) || $task['task_type'] == 'invite_success' || $task['task_type'] == 'first_recharge' || $task['task_type'] == 'first_order'): ?>
                <div class="task-params" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                    <div style="font-weight: bold; margin-bottom: 10px; color: #666;">参数配置:</div>
                    
                    <?php if ($task['task_type'] == 'invite_success'): ?>
                        <div class="am-form-group">
                            <label class="am-form-label" style="display: inline-block; width: auto; margin-right: 10px;">
                                最低邀请人数:
                            </label>
                            <input type="number" 
                                   name="task_config[<?= $role_type ?>][<?= $task['id'] ?>][task_params][min_invites]" 
                                   value="<?= isset($taskParams['min_invites']) ? $taskParams['min_invites'] : 1 ?>" 
                                   class="am-form-field" 
                                   style="width: 150px; display: inline-block;"
                                   min="1"
                                   step="1"
                                   placeholder="1">
                            <span class="am-text-secondary">人</span>
                        </div>
                    <?php elseif ($task['task_type'] == 'first_recharge'): ?>
                        <div class="am-form-group">
                            <label class="am-form-label" style="display: inline-block; width: auto; margin-right: 10px;">
                                最低充值金额:
                            </label>
                            <input type="number" 
                                   name="task_config[<?= $role_type ?>][<?= $task['id'] ?>][task_params][min_amount]" 
                                   value="<?= isset($taskParams['min_amount']) ? $taskParams['min_amount'] : 100 ?>" 
                                   class="am-form-field" 
                                   style="width: 150px; display: inline-block;"
                                   min="0"
                                   step="0.01"
                                   placeholder="100">
                            <span class="am-text-secondary">泰铢</span>
                        </div>
                    <?php elseif ($task['task_type'] == 'first_order'): ?>
                        <div class="am-form-group">
                            <label class="am-form-label" style="display: inline-block; width: auto; margin-right: 10px;">
                                最低订单金额:
                            </label>
                            <input type="number" 
                                   name="task_config[<?= $role_type ?>][<?= $task['id'] ?>][task_params][min_amount]" 
                                   value="<?= isset($taskParams['min_amount']) ? $taskParams['min_amount'] : 0 ?>" 
                                   class="am-form-field" 
                                   style="width: 150px; display: inline-block;"
                                   min="0"
                                   step="0.01"
                                   placeholder="0">
                            <span class="am-text-secondary">泰铢</span>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <?php
}
?>
