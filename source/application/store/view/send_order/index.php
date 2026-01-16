<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">发货列表 <a href="/index.php?s=/store/send_order/presend_list" style="font-size:12px;">去预发货列表</a></div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>发货单号</th>
                                <th>包裹数量</th>
                                <th>操作人员</th>
                                <th>打包封箱时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['send_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['order_sn'] ?></td>
                                        <td class="am-text-middle"><?= $item['num'] ?></td>
                                        <td class="am-text-middle">管理员</td>
                                        <td class="am-text-middle"><?= $item['created_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('sendOrder/detailsItem')): ?>
                                                    <a href="<?= url('sendOrder/detailsItem',
                                                        ['send_id' => $item['send_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 包裹详情
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= url('sendOrder/logistics',
                                                        ['send_id' => $item['send_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 物流更新
                                                </a>
                                                <?php if (checkPrivilege('sendOrder/sendOrderdelete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['send_id'] ?>" onclick="tools.init(this)" data-mode='request' data-confirm=true data-confirm_text='请确认是否删除！' data-refresh=true data-url='<?= url('store/package.line/delete') ?>'>
                                                        <i class="am-icon-trash"></i> 删除
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="am-text-center">暂无记录</td>
                                </tr>
                            <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="am-u-lg-12 am-cf">
                        <div class="am-fr"><?= $list->render() ?> </div>
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
    $(function () {

    });
</script>

