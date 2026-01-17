<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-fl">推荐关系管理</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 搜索表单 -->
                    <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
                        <div class="am-form-group">
                            <form class="am-form am-form-horizontal" action="<?= url('setting.referral/relations') ?>" method="get">
                                <div class="am-u-sm-12 am-u-md-3">
                                    <input type="text" class="am-form-field" name="referrer_user_id" placeholder="推荐人用户ID" value="<?= $search['referrer_user_id'] ?? '' ?>">
                                </div>
                                <div class="am-u-sm-12 am-u-md-3">
                                    <input type="text" class="am-form-field" name="referee_user_id" placeholder="被推荐人用户ID" value="<?= $search['referee_user_id'] ?? '' ?>">
                                </div>
                                <div class="am-u-sm-12 am-u-md-2">
                                    <select class="am-form-field" name="status">
                                        <option value="">全部状态</option>
                                        <option value="1" <?= isset($search['status']) && $search['status'] == '1' ? 'selected' : '' ?>>待完成</option>
                                        <option value="2" <?= isset($search['status']) && $search['status'] == '2' ? 'selected' : '' ?>>已完成</option>
                                        <option value="3" <?= isset($search['status']) && $search['status'] == '3' ? 'selected' : '' ?>>已失效</option>
                                    </select>
                                </div>
                                <div class="am-u-sm-12 am-u-md-2">
                                    <select class="am-form-field" name="level">
                                        <option value="">全部级别</option>
                                        <option value="1" <?= isset($search['level']) && $search['level'] == '1' ? 'selected' : '' ?>>1级</option>
                                        <option value="2" <?= isset($search['level']) && $search['level'] == '2' ? 'selected' : '' ?>>2级</option>
                                        <option value="3" <?= isset($search['level']) && $search['level'] == '3' ? 'selected' : '' ?>>3级</option>
                                    </select>
                                </div>
                                <div class="am-u-sm-12 am-u-md-2">
                                    <button type="submit" class="am-btn am-btn-secondary am-btn-block">搜索</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- 数据表格 -->
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>推荐人</th>
                                    <th>被推荐人</th>
                                    <th>推荐码</th>
                                    <th>级别</th>
                                    <th>推荐人任务</th>
                                    <th>被推荐人任务</th>
                                    <th>状态</th>
                                    <th>奖励发放</th>
                                    <th>创建时间</th>
                                    <th>操作</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($list)): foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['id'] ?></td>
                                        <td class="am-text-middle">
                                            <div>ID: <?= $item['referrer_user_id'] ?></div>
                                            <?php if (isset($item['referrer_info'])): ?>
                                                <div class="am-text-xs am-text-secondary"><?= $item['referrer_info']['nickName'] ?? '' ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <div>ID: <?= $item['referee_user_id'] ?></div>
                                            <?php if (isset($item['referee_info'])): ?>
                                                <div class="am-text-xs am-text-secondary"><?= $item['referee_info']['nickName'] ?? '' ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <span class="am-badge am-badge-secondary"><?= $item['referral_code'] ?></span>
                                        </td>
                                        <td class="am-text-middle">
                                            <?= $item['level'] ?>级
                                        </td>
                                        <td class="am-text-middle">
                                            <?php if ($item['referrer_task_status'] == 1): ?>
                                                <span class="am-badge am-badge-success">已完成</span>
                                            <?php else: ?>
                                                <span class="am-badge am-badge-warning">未完成</span>
                                            <?php endif; ?>
                                            <?php if ($item['referrer_task_complete_time']): ?>
                                                <div class="am-text-xs am-text-secondary"><?= date('Y-m-d H:i', $item['referrer_task_complete_time']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php if ($item['referee_task_status'] == 1): ?>
                                                <span class="am-badge am-badge-success">已完成</span>
                                            <?php else: ?>
                                                <span class="am-badge am-badge-warning">未完成</span>
                                            <?php endif; ?>
                                            <?php if ($item['referee_task_complete_time']): ?>
                                                <div class="am-text-xs am-text-secondary"><?= date('Y-m-d H:i', $item['referee_task_complete_time']) ?></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php
                                            $statusMap = [1 => '待完成', 2 => '已完成', 3 => '已失效'];
                                            $statusClass = [1 => 'warning', 2 => 'success', 3 => 'secondary'];
                                            ?>
                                            <span class="am-badge am-badge-<?= $statusClass[$item['status']] ?>">
                                                <?= $statusMap[$item['status']] ?>
                                            </span>
                                        </td>
                                        <td class="am-text-middle">
                                            <?php if ($item['reward_issued']): ?>
                                                <span class="am-badge am-badge-success">已发放</span>
                                                <?php if ($item['reward_issue_time']): ?>
                                                    <div class="am-text-xs am-text-secondary"><?= date('Y-m-d H:i', $item['reward_issue_time']) ?></div>
                                                <?php endif; ?>
                                            <?php else: ?>
                                                <span class="am-badge am-badge-secondary">未发放</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <?= date('Y-m-d H:i', $item['create_time']) ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if ($item['status'] == 1): ?>
                                                    <a href="javascript:;" class="tpl-table-black-operation-del j-invalidate" data-id="<?= $item['id'] ?>">
                                                        <i class="am-icon-ban"></i> 失效
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; else: ?>
                                    <tr>
                                        <td colspan="11" class="am-text-center">暂无数据</td>
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
    // 失效推荐关系
    $('.j-invalidate').click(function() {
        var relationId = $(this).data('id');
        layer.confirm('确定要使该推荐关系失效吗？', {
            btn: ['确定', '取消']
        }, function(index) {
            $.post('<?= url('setting.referral/invalidate') ?>', {
                relation_id: relationId
            }, function(result) {
                result.code === 1 ? $.show_success(result.msg, result.url)
                    : $.show_error(result.msg);
            });
            layer.close(index);
        });
    });
});
</script>
