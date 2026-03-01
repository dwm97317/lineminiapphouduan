<!-- 账单信息列组件 -->
<td class="am-text-middle">
    <?php 
    // Inpack订单：直接使用自己的账单信息
    $hasStatement = false;
    $statementInfo = null;
    if (!empty($item['statement_id'])) {
        // 查询账单信息
        $statementInfo = \app\store\model\Statement::where('id', $item['statement_id'])
            ->field('id,statement_no,pay_status')
            ->find();
        if ($statementInfo) {
            $hasStatement = true;
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
