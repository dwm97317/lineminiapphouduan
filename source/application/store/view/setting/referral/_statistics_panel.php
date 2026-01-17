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

<script>
$(function() {
    $('.statistics-card').on('click', function() {
        var targetTab = $(this).parent().data('target-tab');
        if (targetTab) {
            $('.am-tabs-nav a[href="#' + targetTab + '"]').trigger('click');
        }
    });
});
</script>
