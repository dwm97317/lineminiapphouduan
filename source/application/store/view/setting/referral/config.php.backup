<?php
// 引入组件文件 - 使用绝对路径避免模板编译问题
$viewPath = dirname(__FILE__);
include $viewPath . '/_task_card.php';
include $viewPath . '/_reward_card.php';
?>

<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <!-- 统计面板 -->
            <?php include __DIR__ . '/_statistics_panel.php'; ?>
            
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
                                                        <?php renderTaskCard($task, 'referrer'); ?>
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
                                                        <?php renderTaskCard($task, 'referee'); ?>
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
                                                <?php renderRewardCard($config); ?>
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
