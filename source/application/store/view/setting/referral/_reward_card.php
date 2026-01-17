<?php
/**
 * 奖励卡片组件
 * @param array $config 奖励配置数据
 */
function renderRewardCard($config) {
    // 解析 reward_params
    $rewardParams = [];
    if (!empty($config['reward_params'])) {
        if (is_string($config['reward_params'])) {
            $rewardParams = json_decode($config['reward_params'], true) ?: [];
        } elseif (is_array($config['reward_params'])) {
            $rewardParams = $config['reward_params'];
        }
    }
    
    // 奖励类型图标和名称
    $rewardTypeMap = [
        1 => ['icon' => '💰', 'name' => '现金'],
        2 => ['icon' => '⭐', 'name' => '积分'],
        3 => ['icon' => '🎫', 'name' => '优惠券']
    ];
    $rewardInfo = $rewardTypeMap[$config['reward_type']] ?? ['icon' => '🎁', 'name' => '未知'];
    
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
                        <option value="1" <?= $config['reward_type'] == 1 ? 'selected' : '' ?>>💰 现金</option>
                        <option value="2" <?= $config['reward_type'] == 2 ? 'selected' : '' ?>>⭐ 积分</option>
                        <option value="3" <?= $config['reward_type'] == 3 ? 'selected' : '' ?>>🎫 优惠券</option>
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
                        <?= $config['reward_type'] == 1 ? '泰铢' : ($config['reward_type'] == 2 ? '积分' : '张') ?>
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
                
                <?php if ($config['reward_type'] == 3): ?>
                    <!-- 优惠券类型的参数 -->
                    <div class="am-form-group param-coupon">
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
                <?php endif; ?>

                <?php if ($config['reward_type'] == 1): ?>
                    <!-- 现金类型的参数 -->
                    <div class="am-form-group param-cash">
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
                <?php endif; ?>

                <?php if ($config['reward_type'] == 2): ?>
                    <!-- 积分类型的参数 -->
                    <div class="am-form-group param-points">
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
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <?php
}
?>
