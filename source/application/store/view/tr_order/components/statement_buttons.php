<!-- 账单操作按钮组件 -->
<!--生成账单-->
<?php if (checkPrivilege('package.statement/create')): ?>
<button type="button" id="j-create-statement" class="am-btn am-btn-primary am-radius">
    <i class="iconfont icon-dingdan"></i> 生成账单
</button>
<?php endif;?>
<!--查看账单-->
<a href="<?= url('finance.config/index') ?>#tab-statement-list" class="am-btn am-btn-default am-radius">
    <i class="iconfont icon-liebiao"></i> 账单列表
</a>
