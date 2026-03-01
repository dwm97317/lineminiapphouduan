<!-- 账单信息列组件 -->
<td class="am-text-middle">
    <?php 
    // Inpack订单：检查其包含的包裹是否有账单
    $hasStatement = false;
    $statementInfo = null;
    if (!empty($item['pack_ids'])) {
        $packageIds = explode(',', $item['pack_ids']);
        // 查询第一个有账单的包裹
        $package = \app\store\model\Package::where('id', 'in', $packageIds)
            ->where('statement_id', '>', 0)
            ->with(['statement' => function($query) {
                $query->field('id,statement_no,pay_status');
            }])
            ->find();
        if ($package && $package['statement_id']) {
            $hasStatement = true;
            $statementInfo = $package['statement'];
        }
    }
    ?>
    <?php if ($hasStatement && $statementInfo): ?>
        <a href="<?= url('package.statement/detail', ['statement_id' => $statementInfo['id']]) ?>" 
           style="color:#1686ef">
            <?= $statementInfo['statement_no'] ?>
        </a></br>
        <?php if ($statementInfo['pay_status'] == 2): ?>
            <span class="am-badge am-badge-success">已支付</span>
        <?php else: ?>
            <span class="am-badge am-badge-warning">未支付</span>
        <?php endif; ?>
    <?php else: ?>
        <span class="am-text-muted">未出账</span>
    <?php endif; ?>
</td>
