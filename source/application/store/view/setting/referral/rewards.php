<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">推荐奖励记录</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 搜索表单 -->
                    <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
                        <div class="am-form-group">
                            <form class="am-form am-form-horizontal" action="<?= url('setting.referral/rewards') ?>" method="get">
                                <div class="am-u-sm-12 am-u-md-2">
                                    <input type="text" class="am-form-field" name="user_id" placeholder="用户ID" value="<?= $search['user_id'] ?? '' ?>">
                                </div>
                                <div class="am-u-sm-12 am-u-md-2">
                                    <select class="am-form-field" name="user_type">
                                        <option value="">全部类型</option>
                                        <option value="1" <?= isset($search['user_type']) && $search['user_type'] == '1' ? 'selected' : '' ?>>推荐人</option>
                                        <option value="2" <?= isset($search['user_type']) && $search['user_type'] == '2' ? 'selected' : '' ?>>被推荐人</option>
                                    </select>
                                </div>
                                <div class="am-u-sm-12 am-u-md-2">
                                    <select class="am-form-field" name="reward_type">
                                        <option value="">全部奖励</option>
                                        <option value="1" <?= isset($search['reward_type']) && $search['reward_type'] == '1' ? 'selected' : '' ?>>现金</option>
                                        <option value="2" <?= isset($search['reward_type']) && $search['reward_type'] == '2' ? 'selected' : '' ?>>积分</option>
                                        <option value="3" <?= isset($search['reward_type']) && $search['reward_type'] == '3' ? 'selected' : '' ?>>优惠券</option>
                                    </select>
                                </div>
                                <div class="am-u-sm-12 am-u-md-2">
                                    <select class="am-form-field" name="status">
                                        <option value="">全部状态</option>
                                        <option value="1" <?= isset($search['status']) && $search['status'] == '1' ? 'selected' : '' ?>>待发放</option>
                                        <option value="2" <?= isset($search['status']) && $search['status'] == '2' ? 'selected' : '' ?>>已发放</option>
                                        <option value="3" <?= isset($search['status']) && $search['status'] == '3' ? 'selected' : '' ?>>已回收</option>
                                    </select>
                                </div>
                                <div class="am-u-sm-12 am-u-md-2">
                                    <button type="submit" class="am-btn am-btn-secondary am-btn-block">搜索</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 统计信息 -->
                    <?php if (isset($statistics)): ?>
                        <div class="am-u-sm-12 am-margin-bottom">
                            <div class="am-g">
                                <div class="am-u-sm-3">
                                    <div class="am-panel am-panel-default">
                                        <div class="am-panel-bd">
                                            <div class="am-text-center">
                                                <div class="am-text-lg am-text-primary">¥<?= number_format($statistics['total_cash'], 2) ?></div>
                                                <div class="am-text-xs am-text-secondary">现金奖励总额</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="am-u-sm-3">
                                    <div class="am-panel am-panel-default">
                                        <div class="am-panel-bd">
                                            <div class="am-text-center">
                                                <div class="am-text-lg am-text-success"><?= $statistics['total_points'] ?></div>
                                                <div class="am-text-xs am-text-secondary">积分奖励总额</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="am-u-sm-3">
                                    <div class="am-panel am-panel-default">
                                        <div class="am-panel-bd">
                                            <div class="am-text-center">
                                                <div class="am-text-lg am-text-warning"><?= $statistics['total_coupons'] ?></div>
                                                <div class="am-text-xs am-text-secondary">优惠券发放数</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="am-u-sm-3">
                                    <div class="am-panel am-panel-default">
                                        <div class="am-panel-bd">
                                            <div class="am-text-center">
                                                <div class="am-text-lg"><?= $statistics['total_count'] ?></div>
                                                <div class="am-text-xs am-text-secondary">奖励记录总数</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- 数据表格 -->
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>推荐关系ID</th>
                                    <th>用户</th>
                                    <th>用户类型</th>
                                    <th>奖励类型</th>
                                    <th>奖励金额/数量</th>
                                    <th>状态</th>
                                    <th>发放时间</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($list)): foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle"><?= $item['relation_id'] ?></td>
                                        <td class="am-text-middle">
                                            <div>ID: <?= $item['user_id'] ?></div>
                                            <?php if (isset($item['user_info'])): ?>
                                                <div class="am-text-xs am-text-secondary"><?= $item['user_info']['nickName'] ?? '' ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php
                                            $userTypeMap = [1 => '推荐人', 2 => '被推荐人'];
                                            ?>
                                            <span class="am-badge am-badge-<?= $item['user_type'] == 1 ? 'primary' : 'secondary' ?>">
                                                <?= $userTypeMap[$item['user_type']] ?>
                                            </span>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php
                                            $rewardTypeMap = [1 => '现金', 2 => '积分', 3 => '优惠券'];
                                            $rewardTypeClass = [1 => 'success', 2 => 'warning', 3 => 'primary'];
                                            ?>
                                            <span class="am-badge am-badge-<?= $rewardTypeClass[$item['reward_type']] ?>">
                                                <?= $rewardTypeMap[$item['reward_type']] ?>
                                            </span>
                                        </td>
                                        <td class="am-text-middle">
                                            <?= $item['reward_type'] == 1 ? '¥' . number_format($item['reward_amount'], 2) : $item['reward_amount'] ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php
                                            $statusMap = [1 => '待发放', 2 => '已发放', 3 => '已回收'];
                                            $statusClass = [1 => 'secondary', 2 => 'success', 3 => 'warning'];
                                            ?>
                                            <span class="am-badge am-badge-<?= $statusClass[$item['status']] ?>">
                                                <?= $statusMap[$item['status']] ?>
                                            </span>
                                            <?php if ($item['status'] == 3 && $item['recycle_reason']): ?>
                                                <div class="am-text-xs am-text-secondary" title="<?= $item['recycle_reason'] ?>">
                                                    <?= mb_substr($item['recycle_reason'], 0, 10) ?>...
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?= $item['issue_time'] ? date('Y-m-d H:i', $item['issue_time']) : '-' ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?= date('Y-m-d H:i', $item['create_time']) ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if ($item['status'] == 2): ?>
                                                    <a href="javascript:;" class="tpl-table-black-operation-del j-recycle" data-id="<?= $item['id'] ?>">
                                                        <i class="am-icon-undo"></i> 回收
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="10" class="am-text-center">暂无数据</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- 分页 -->
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr">
                            <?= $list->render() ?>
                        </div>
                        <div class="am-fr pagination-total am-margin-right">
                            <div class="am-vertical-align-middle">总记录：<?= $list->total() ?></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
$(function() {
    // 回收奖励
    $('.j-recycle').click(function() {
        var rewardId = $(this).data('id');
        layer.prompt({
            title: '请输入回收原因',
            formType: 2
        }, function(reason, index) {
            if (!reason) {
                layer.msg('请输入回收原因');
                return;
            }
            $.post('<?= url('setting.referral/recycle') ?>', {
                reward_id: rewardId,
                reason: reason
            }, function(result) {
                result.code === 1 ? $.show_success(result.msg, result.url)
                    : $.show_error(result.msg);
            });
            layer.close(index);
        });
    });
});
</script>
