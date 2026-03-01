<!-- 账单状态筛选器组件 -->
<div class="am-form-group am-fl">
    <?php $statementStatus = $request->get('statement_status'); ?>
    <select name="statement_status"
            data-am-selected="{btnSize: 'sm', placeholder: '账单状态'}">
        <option value="">账单状态</option>
        <option value="unbilled" <?= $statementStatus === 'unbilled' ? 'selected' : '' ?>>未出账</option>
        <option value="unpaid" <?= $statementStatus === 'unpaid' ? 'selected' : '' ?>>已出账未支付</option>
        <option value="paid" <?= $statementStatus === 'paid' ? 'selected' : '' ?>>已支付</option>
    </select>
</div>
