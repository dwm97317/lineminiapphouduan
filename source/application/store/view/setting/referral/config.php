<?php
// 计算统计数据
$total_tasks = (isset($task_configs['referrer']) ? count($task_configs['referrer']) : 0) + 
               (isset($task_configs['referee']) ? count($task_configs['referee']) : 0);
$enabled_tasks = 0;
if (isset($task_configs['referrer'])) {
    foreach ($task_configs['referrer'] as $task) {
        if ($task['is_enabled']) $enabled_tasks++;
    }
}
if (isset($task_configs['referee'])) {
    foreach ($task_configs['referee'] as $task) {
        if ($task['is_enabled']) $enabled_tasks++;
    }
}

$total_rewards = isset($reward_configs) ? count($reward_configs) : 0;
$enabled_rewards = 0;
if (isset($reward_configs)) {
    foreach ($reward_configs as $config) {
        if ($config['is_enabled']) $enabled_rewards++;
    }
}

$max_levels = $system_config['max_referral_levels'] ?? 1;
$referrer_tasks = isset($task_configs['referrer']) ? count($task_configs['referrer']) : 0;
?>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <!-- 统计面板 -->
            <div class="am-panel am-panel-default" style="margin-bottom: 20px;">
                <div class="am-panel-hd">系统配置概览</div>
                <div class="am-panel-bd">
                    <div class="am-g">
                        <div class="am-u-sm-6 am-u-md-3" style="cursor: pointer;" data-target-tab="tab2">
                            <div class="statistics-card" style="text-align: center; padding: 20px; border-right: 1px solid #e5e5e5;">
                                <div class="icon" style="font-size: 36px; color: #0e90d2; margin-bottom: 10px;">📋</div>
                                <div class="value" style="font-size: 24px; font-weight: bold; color: #333;">
                                    <?= $enabled_tasks ?>/<?= $total_tasks ?>
                                </div>
                                <div class="label" style="font-size: 14px; color: #999;">启用任务数</div>
                            </div>
                        </div>
                        
                        <div class="am-u-sm-6 am-u-md-3" style="cursor: pointer;" data-target-tab="tab3">
                            <div class="statistics-card" style="text-align: center; padding: 20px; border-right: 1px solid #e5e5e5;">
                                <div class="icon" style="font-size: 36px; color: #5eb95e; margin-bottom: 10px;">🎁</div>
                                <div class="value" style="font-size: 24px; font-weight: bold; color: #333;">
                                    <?= $enabled_rewards ?>/<?= $total_rewards ?>
                                </div>
                                <div class="label" style="font-size: 14px; color: #999;">奖励配置数</div>
                            </div>
                        </div>
                        
                        <div class="am-u-sm-6 am-u-md-3">
                            <div class="statistics-card" style="text-align: center; padding: 20px; border-right: 1px solid #e5e5e5;">
                                <div class="icon" style="font-size: 36px; color: #f37b1d; margin-bottom: 10px;">📊</div>
                                <div class="value" style="font-size: 24px; font-weight: bold; color: #333;">
                                    <?= $max_levels ?>级
                                </div>
                                <div class="label" style="font-size: 14px; color: #999;">推荐级数</div>
                            </div>
                        </div>
                        
                        <div class="am-u-sm-6 am-u-md-3">
                            <div class="statistics-card" style="text-align: center; padding: 20px;">
                                <div class="icon" style="font-size: 36px; color: #dd514c; margin-bottom: 10px;">👥</div>
                                <div class="value" style="font-size: 24px; font-weight: bold; color: #333;">
                                    <?= $referrer_tasks ?>
                                </div>
                                <div class="label" style="font-size: 14px; color: #999;">推荐人任务</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">推荐奖励系统配置</div>
                </div>
                <div class="widget-body">
                    <div class="am-tabs" data-am-tabs>
                        <ul class="am-tabs-nav am-nav am-nav-tabs">
                            <li class="am-active"><a href="#tab1">系统配置</a></li>
                            <li><a href="#tab2">任务配置</a></li>
                            <li><a href="#tab3">奖励配置</a></li>
                        </ul>

                        <div class="am-tabs-bd">
                            <!-- 系统配置 -->
                            <div class="am-tab-panel am-fade am-in am-active" id="tab1">
                                <form action="<?= url('setting.referral/saveConfig') ?>" class="am-form tpl-form-line-form" method="post">
                                    <input type="hidden" name="config_type" value="system">
                                    <fieldset>
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">推荐系统全局配置</div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">最大推荐级数</label>
                                            <div class="am-u-sm-9">
                                                <select class="am-form-field tpl-form-input" name="system_config[max_referral_levels]" required>
                                                    <option value="1" <?= isset($system_config['max_referral_levels']) && $system_config['max_referral_levels'] == '1' ? 'selected' : '' ?>>1级 (仅直接推荐)</option>
                                                    <option value="2" <?= isset($system_config['max_referral_levels']) && $system_config['max_referral_levels'] == '2' ? 'selected' : '' ?>>2级</option>
                                                    <option value="3" <?= isset($system_config['max_referral_levels']) && $system_config['max_referral_levels'] == '3' ? 'selected' : '' ?>>3级</option>
                                                </select>
                                                <small>设置推荐关系的最大层级数</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">推荐码长度</label>
                                            <div class="am-u-sm-9">
                                                <select class="am-form-field tpl-form-input" name="system_config[referral_code_length]" required>
                                                    <option value="6" <?= isset($system_config['referral_code_length']) && $system_config['referral_code_length'] == '6' ? 'selected' : '' ?>>6位</option>
                                                    <option value="7" <?= isset($system_config['referral_code_length']) && $system_config['referral_code_length'] == '7' ? 'selected' : '' ?>>7位</option>
                                                    <option value="8" <?= isset($system_config['referral_code_length']) && $system_config['referral_code_length'] == '8' ? 'selected' : '' ?>>8位</option>
                                                </select>
                                                <small>推荐码字符长度</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label form-require">推荐关系失效天数</label>
                                            <div class="am-u-sm-9">
                                                <input type="number" class="tpl-form-input" name="system_config[expire_days]" value="<?= $system_config['expire_days'] ?? '30' ?>" min="1" max="365" required>
                                                <small>推荐关系在多少天内未完成任务将自动失效</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">启用推荐上限</label>
                                            <div class="am-u-sm-9">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="system_config[referral_limit_enabled]" value="1" data-am-ucheck <?= isset($system_config['referral_limit_enabled']) && $system_config['referral_limit_enabled'] == '1' ? 'checked' : '' ?>> 启用
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="system_config[referral_limit_enabled]" value="0" data-am-ucheck <?= !isset($system_config['referral_limit_enabled']) || $system_config['referral_limit_enabled'] == '0' ? 'checked' : '' ?>> 禁用
                                                </label>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">每月推荐上限</label>
                                            <div class="am-u-sm-9">
                                                <input type="number" class="tpl-form-input" name="system_config[referral_limit_per_month]" value="<?= $system_config['referral_limit_per_month'] ?? '100' ?>" min="1">
                                                <small>每个用户每月最多可推荐的人数</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">启用排行榜</label>
                                            <div class="am-u-sm-9">
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="system_config[leaderboard_enabled]" value="1" data-am-ucheck <?= isset($system_config['leaderboard_enabled']) && $system_config['leaderboard_enabled'] == '1' ? 'checked' : '' ?>> 启用
                                                </label>
                                                <label class="am-radio-inline">
                                                    <input type="radio" name="system_config[leaderboard_enabled]" value="0" data-am-ucheck <?= !isset($system_config['leaderboard_enabled']) || $system_config['leaderboard_enabled'] == '0' ? 'checked' : '' ?>> 禁用
                                                </label>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <label class="am-u-sm-3 am-form-label">排行榜显示人数</label>
                                            <div class="am-u-sm-9">
                                                <input type="number" class="tpl-form-input" name="system_config[leaderboard_top_count]" value="<?= $system_config['leaderboard_top_count'] ?? '50' ?>" min="10" max="100">
                                                <small>排行榜显示的最大人数</small>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3">
                                                <button type="submit" class="j-submit am-btn am-btn-secondary">保存系统配置</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>

                            <!-- 任务配置 -->
                            <div class="am-tab-panel am-fade" id="tab2">
                                <form action="<?= url('setting.referral/saveConfig') ?>" class="am-form tpl-form-line-form" method="post">
                                    <input type="hidden" name="config_type" value="task">
                                    <fieldset>
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">推荐任务配置</div>
                                        </div>

                                        <div class="am-panel am-panel-primary" style="border-radius: 8px; margin-bottom: 20px;">
                                            <div class="am-panel-hd" style="font-size: 16px; font-weight: bold;">
                                                👤 推荐人任务
                                            </div>
                                            <div class="am-panel-bd">
                                                <?php if (isset($task_configs['referrer']) && !empty($task_configs['referrer'])): ?>
                                                    <?php foreach ($task_configs['referrer'] as $task): ?>
                                                        <?php
                                                        // 解析 task_params
                                                        $taskParams = [];
                                                        if (!empty($task['task_params'])) {
                                                            if (is_string($task['task_params'])) {
                                                                $taskParams = json_decode($task['task_params'], true) ?: [];
                                                            } elseif (is_array($task['task_params'])) {
                                                                $taskParams = $task['task_params'];
                                                            }
                                                        }
                                                        
                                                        // 获取任务类型的值（处理访问器返回的数组）
                                                        $taskType = is_array($task['task_type']) ? $task['task_type']['value'] : $task['task_type'];
                                                        
                                                        // 任务类型映射
                                                        $taskTypeMap = [
                                                            'invite_success' => '成功邀请用户',
                                                            'first_recharge' => '首次充值',
                                                            'first_order' => '首次下单',
                                                            'complete_profile' => '完善资料'
                                                        ];
                                                        $taskTypeName = $taskTypeMap[$taskType] ?? $taskType;
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
                                                                               name="task_config[referrer][<?= $task['id'] ?>][is_enabled]" 
                                                                               value="1" 
                                                                               <?= $task['is_enabled'] ? 'checked' : '' ?> 
                                                                               data-am-ucheck> 启用
                                                                    </label>
                                                                    <label class="am-checkbox-inline">
                                                                        <input type="checkbox" 
                                                                               name="task_config[referrer][<?= $task['id'] ?>][is_required]" 
                                                                               value="1" 
                                                                               <?= $task['is_required'] ? 'checked' : '' ?> 
                                                                               data-am-ucheck> 必须完成
                                                                    </label>
                                                                </div>
                                                                
                                                                <?php if ($taskType == 'invite_success'): ?>
                                                                    <div class="task-params" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                                                                        <div style="font-weight: bold; margin-bottom: 10px; color: #666;">参数配置:</div>
                                                                        <div class="am-form-group">
                                                                            <label class="am-form-label" style="display: inline-block; width: auto; margin-right: 10px;">
                                                                                最低邀请人数:
                                                                            </label>
                                                                            <input type="number" 
                                                                                   name="task_config[referrer][<?= $task['id'] ?>][task_params][min_invites]" 
                                                                                   value="<?= isset($taskParams['min_invites']) ? $taskParams['min_invites'] : 1 ?>" 
                                                                                   class="am-form-field" 
                                                                                   style="width: 150px; display: inline-block;"
                                                                                   min="1"
                                                                                   step="1"
                                                                                   placeholder="1">
                                                                            <span class="am-text-secondary">人</span>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <p class="am-text-center am-text-secondary" style="padding: 20px;">
                                                        暂无推荐人任务配置
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="am-panel am-panel-success" style="border-radius: 8px; margin-bottom: 20px;">
                                            <div class="am-panel-hd" style="font-size: 16px; font-weight: bold;">
                                                🎯 被推荐人任务
                                            </div>
                                            <div class="am-panel-bd">
                                                <?php if (isset($task_configs['referee']) && !empty($task_configs['referee'])): ?>
                                                    <?php foreach ($task_configs['referee'] as $task): ?>
                                                        <?php
                                                        // 解析 task_params
                                                        $taskParams = [];
                                                        if (!empty($task['task_params'])) {
                                                            if (is_string($task['task_params'])) {
                                                                $taskParams = json_decode($task['task_params'], true) ?: [];
                                                            } elseif (is_array($task['task_params'])) {
                                                                $taskParams = $task['task_params'];
                                                            }
                                                        }
                                                        
                                                        // 获取任务类型的值（处理访问器返回的数组）
                                                        $taskType = is_array($task['task_type']) ? $task['task_type']['value'] : $task['task_type'];
                                                        
                                                        // 任务类型映射
                                                        $taskTypeMap = [
                                                            'invite_success' => '成功邀请用户',
                                                            'first_recharge' => '首次充值',
                                                            'first_order' => '首次下单',
                                                            'complete_profile' => '完善资料'
                                                        ];
                                                        $taskTypeName = $taskTypeMap[$taskType] ?? $taskType;
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
                                                                               name="task_config[referee][<?= $task['id'] ?>][is_enabled]" 
                                                                               value="1" 
                                                                               <?= $task['is_enabled'] ? 'checked' : '' ?> 
                                                                               data-am-ucheck> 启用
                                                                    </label>
                                                                    <label class="am-checkbox-inline">
                                                                        <input type="checkbox" 
                                                                               name="task_config[referee][<?= $task['id'] ?>][is_required]" 
                                                                               value="1" 
                                                                               <?= $task['is_required'] ? 'checked' : '' ?> 
                                                                               data-am-ucheck> 必须完成
                                                                    </label>
                                                                </div>
                                                                
                                                                <?php if ($taskType == 'first_recharge' || $taskType == 'first_order'): ?>
                                                                    <div class="task-params" style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                                                                        <div style="font-weight: bold; margin-bottom: 10px; color: #666;">参数配置:</div>
                                                                        <div class="am-form-group">
                                                                            <label class="am-form-label" style="display: inline-block; width: auto; margin-right: 10px;">
                                                                                <?= $taskType == 'first_recharge' ? '最低充值金额:' : '最低订单金额:' ?>
                                                                            </label>
                                                                            <input type="number" 
                                                                                   name="task_config[referee][<?= $task['id'] ?>][task_params][min_amount]" 
                                                                                   value="<?= isset($taskParams['min_amount']) ? $taskParams['min_amount'] : ($taskType == 'first_recharge' ? 100 : 0) ?>" 
                                                                                   class="am-form-field" 
                                                                                   style="width: 150px; display: inline-block;"
                                                                                   min="0"
                                                                                   step="0.01"
                                                                                   placeholder="<?= $taskType == 'first_recharge' ? '100' : '0' ?>">
                                                                            <span class="am-text-secondary">泰铢</span>
                                                                        </div>
                                                                    </div>
                                                                <?php endif; ?>
                                                            </div>
                                                        </div>
                                                    <?php endforeach; ?>
                                                <?php else: ?>
                                                    <p class="am-text-center am-text-secondary" style="padding: 20px;">
                                                        暂无被推荐人任务配置
                                                    </p>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3">
                                                <button type="submit" class="j-submit am-btn am-btn-secondary">保存任务配置</button>
                                            </div>
                                        </div>
                                    </fieldset>
                                </form>
                            </div>

                            <!-- 奖励配置 -->
                            <div class="am-tab-panel am-fade" id="tab3">
                                <form action="<?= url('setting.referral/saveConfig') ?>" class="am-form tpl-form-line-form" method="post">
                                    <input type="hidden" name="config_type" value="reward">
                                    <fieldset>
                                        <div class="widget-head am-cf">
                                            <div class="widget-title am-fl">推荐奖励配置</div>
                                        </div>

                                        <?php if (isset($reward_configs) && !empty($reward_configs)): ?>
                                            <?php foreach ($reward_configs as $config): ?>
                                                <?php
                                                // 解析 reward_params
                                                $rewardParams = [];
                                                if (!empty($config['reward_params'])) {
                                                    if (is_string($config['reward_params'])) {
                                                        $rewardParams = json_decode($config['reward_params'], true) ?: [];
                                                    } elseif (is_array($config['reward_params'])) {
                                                        $rewardParams = $config['reward_params'];
                                                    }
                                                }
                                                
                                                // 获取奖励类型的值（处理访问器返回的数组）
                                                $rewardType = is_array($config['reward_type']) ? $config['reward_type']['value'] : $config['reward_type'];
                                                $rewardType = (int)$rewardType; // 确保是整数
                                                
                                                // 奖励类型图标和名称
                                                $rewardTypeMap = [
                                                    1 => ['icon' => '💰', 'name' => '现金'],
                                                    2 => ['icon' => '⭐', 'name' => '积分'],
                                                    3 => ['icon' => '🎫', 'name' => '优惠券']
                                                ];
                                                $rewardInfo = $rewardTypeMap[$rewardType] ?? ['icon' => '🎁', 'name' => '未知'];
                                                
                                                $cardOpacity = $config['is_enabled'] ? '1' : '0.5';
                                                ?>
                                                
                                                <div class="am-panel am-panel-default reward-card" 
                                                     style="margin-bottom: 15px; border-radius: 8px; overflow: hidden; opacity: <?= $cardOpacity ?>;">
                                                    <div class="am-panel-hd" 
                                                         style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; padding: 15px;">
                                                        <span class="reward-icon" style="font-size: 24px; margin-right: 10px;">
                                                            <?= $rewardInfo['icon'] ?>
                                                        </span>
                                                        <span class="reward-name" style="font-size: 16px; font-weight: bold;">
                                                            <?= $config['config_name'] ?>
                                                        </span>
                                                        <span class="reward-level" 
                                                              style="float: right; background: rgba(255,255,255,0.3); padding: 3px 10px; border-radius: 12px; font-size: 12px;">
                                                            <?= $config['level'] ?>级推荐
                                                        </span>
                                                    </div>
                                                    
                                                    <div class="am-panel-bd" style="padding: 15px; background: #fff;">
                                                        <input type="hidden" name="reward_config[<?= $config['id'] ?>][id]" value="<?= $config['id'] ?>">
                                                        
                                                        <div class="am-form-group">
                                                            <label class="am-u-sm-3 am-form-label">启用状态</label>
                                                            <div class="am-u-sm-9">
                                                                <label class="am-radio-inline">
                                                                    <input type="radio" 
                                                                           name="reward_config[<?= $config['id'] ?>][is_enabled]" 
                                                                           value="1" 
                                                                           <?= $config['is_enabled'] ? 'checked' : '' ?>> 
                                                                    <span style="color: #5eb95e;">● 启用</span>
                                                                </label>
                                                                <label class="am-radio-inline">
                                                                    <input type="radio" 
                                                                           name="reward_config[<?= $config['id'] ?>][is_enabled]" 
                                                                           value="0" 
                                                                           <?= !$config['is_enabled'] ? 'checked' : '' ?>> 
                                                                    <span style="color: #999;">● 禁用</span>
                                                                </label>
                                                            </div>
                                                        </div>

                                                        <div class="am-form-group">
                                                            <label class="am-u-sm-3 am-form-label">奖励类型</label>
                                                            <div class="am-u-sm-9">
                                                                <select class="am-form-field tpl-form-input reward-type-selector" 
                                                                        name="reward_config[<?= $config['id'] ?>][reward_type]"
                                                                        data-config-id="<?= $config['id'] ?>">
                                                                    <option value="1" <?= $rewardType == 1 ? 'selected' : '' ?>>💰 现金</option>
                                                                    <option value="2" <?= $rewardType == 2 ? 'selected' : '' ?>>⭐ 积分</option>
                                                                    <option value="3" <?= $rewardType == 3 ? 'selected' : '' ?>>🎫 优惠券</option>
                                                                </select>
                                                            </div>
                                                        </div>

                                                        <div class="am-form-group">
                                                            <label class="am-u-sm-3 am-form-label">奖励金额/数量</label>
                                                            <div class="am-u-sm-9">
                                                                <input type="number" 
                                                                       class="tpl-form-input" 
                                                                       name="reward_config[<?= $config['id'] ?>][reward_amount]" 
                                                                       value="<?= $config['reward_amount'] ?>" 
                                                                       min="0" 
                                                                       step="0.01"
                                                                       style="width: 200px; display: inline-block;">
                                                                <span class="am-text-secondary" style="margin-left: 10px;">
                                                                    <?= $rewardType == 1 ? '泰铢' : ($rewardType == 2 ? '积分' : '张') ?>
                                                                </span>
                                                            </div>
                                                        </div>

                                                        <div class="am-form-group">
                                                            <label class="am-u-sm-3 am-form-label">奖励比例</label>
                                                            <div class="am-u-sm-9">
                                                                <input type="number" 
                                                                       class="tpl-form-input" 
                                                                       name="reward_config[<?= $config['id'] ?>][reward_ratio]" 
                                                                       value="<?= $config['reward_ratio'] ?>" 
                                                                       min="0" 
                                                                       max="100" 
                                                                       step="0.01"
                                                                       style="width: 200px; display: inline-block;">
                                                                <span class="am-text-secondary" style="margin-left: 10px;">%</span>
                                                                <small class="am-block" style="color: #999; margin-top: 5px;">
                                                                    用于多级推荐时的奖励比例调整
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <div class="am-form-group">
                                                            <label class="am-u-sm-3 am-form-label">有效期</label>
                                                            <div class="am-u-sm-9">
                                                                <input type="number" 
                                                                       class="tpl-form-input" 
                                                                       name="reward_config[<?= $config['id'] ?>][expire_days]" 
                                                                       value="<?= $config['expire_days'] ?? '' ?>" 
                                                                       min="0"
                                                                       style="width: 200px; display: inline-block;">
                                                                <span class="am-text-secondary" style="margin-left: 10px;">天</span>
                                                                <small class="am-block" style="color: #999; margin-top: 5px;">
                                                                    留空表示永久有效
                                                                </small>
                                                            </div>
                                                        </div>

                                                        <div class="reward-params-<?= $config['id'] ?>" 
                                                             style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e5e5e5;">
                                                            <div style="font-weight: bold; margin-bottom: 10px; color: #666;">参数配置:</div>
                                                            
                                                            <div class="am-form-group param-coupon" style="<?= $rewardType != 3 ? 'display:none;' : '' ?>">
                                                                <label class="am-u-sm-3 am-form-label">优惠券ID</label>
                                                                <div class="am-u-sm-9">
                                                                    <input type="number" 
                                                                           class="tpl-form-input" 
                                                                           name="reward_config[<?= $config['id'] ?>][reward_params][coupon_id]" 
                                                                           value="<?= isset($rewardParams['coupon_id']) ? $rewardParams['coupon_id'] : '' ?>" 
                                                                           min="0"
                                                                           placeholder="输入优惠券ID">
                                                                    <small style="color: #999;">指定发放的优惠券ID</small>
                                                                </div>
                                                            </div>

                                                            <div class="am-form-group param-cash" style="<?= $rewardType != 1 ? 'display:none;' : '' ?>">
                                                                <label class="am-u-sm-3 am-form-label">最低提现金额</label>
                                                                <div class="am-u-sm-9">
                                                                    <input type="number" 
                                                                           class="tpl-form-input" 
                                                                           name="reward_config[<?= $config['id'] ?>][reward_params][min_withdraw]" 
                                                                           value="<?= isset($rewardParams['min_withdraw']) ? $rewardParams['min_withdraw'] : '' ?>" 
                                                                           min="0"
                                                                           step="0.01"
                                                                           placeholder="留空表示无限制">
                                                                    <small style="color: #999;">泰铢，留空表示无最低提现限制</small>
                                                                </div>
                                                            </div>

                                                            <div class="am-form-group param-points" style="<?= $rewardType != 2 ? 'display:none;' : '' ?>">
                                                                <label class="am-u-sm-3 am-form-label">积分有效期</label>
                                                                <div class="am-u-sm-9">
                                                                    <input type="number" 
                                                                           class="tpl-form-input" 
                                                                           name="reward_config[<?= $config['id'] ?>][reward_params][points_expire_days]" 
                                                                           value="<?= isset($rewardParams['points_expire_days']) ? $rewardParams['points_expire_days'] : '' ?>" 
                                                                           min="0"
                                                                           placeholder="留空表示永久有效">
                                                                    <small style="color: #999;">天，积分的有效期，留空表示永久有效</small>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            <?php endforeach; ?>
                                        <?php else: ?>
                                            <div class="am-panel am-panel-default">
                                                <div class="am-panel-bd">
                                                    <p class="am-text-center am-text-secondary" style="padding: 40px;">
                                                        暂无奖励配置
                                                    </p>
                                                </div>
                                            </div>
                                        <?php endif; ?>

                                        <div class="am-form-group">
                                            <div class="am-u-sm-9 am-u-sm-push-3">
                                                <button type="submit" class="j-submit am-btn am-btn-secondary">保存奖励配置</button>
                                            </div>
                                        </div>
                                    </fieldset>
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
    // AJAX表单提交 - 使用superForm处理所有表单
    $('.tpl-form-line-form').superForm();
    
    // 统计卡片点击跳转
    $('.statistics-card').on('click', function() {
        var targetTab = $(this).parent().data('target-tab');
        if (targetTab) {
            $('.am-tabs-nav a[href="#' + targetTab + '"]').trigger('click');
        }
    });
    
    // 奖励类型切换时动态显示/隐藏参数配置
    $('.reward-type-selector').on('change', function() {
        var configId = $(this).data('config-id');
        var rewardType = $(this).val();
        var paramsContainer = $('.reward-params-' + configId);
        
        // 隐藏所有参数
        paramsContainer.find('.param-cash, .param-points, .param-coupon').hide();
        
        // 根据类型显示对应参数
        if (rewardType == '1') {
            paramsContainer.find('.param-cash').show();
        } else if (rewardType == '2') {
            paramsContainer.find('.param-points').show();
        } else if (rewardType == '3') {
            paramsContainer.find('.param-coupon').show();
        }
    });
    
    // 页面加载时初始化显示状态
    $('.reward-type-selector').each(function() {
        $(this).trigger('change');
    });
    
    // 统计卡片悬停效果
    $('.statistics-card').hover(
        function() {
            $(this).css({
                'transform': 'translateY(-5px)',
                'box-shadow': '0 4px 8px rgba(0,0,0,0.15)',
                'transition': 'all 0.3s ease'
            });
        },
        function() {
            $(this).css({
                'transform': 'translateY(0)',
                'box-shadow': '0 2px 4px rgba(0,0,0,0.1)',
                'transition': 'all 0.3s ease'
            });
        }
    );
});
</script>
