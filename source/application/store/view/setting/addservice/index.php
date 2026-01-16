<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">增值服务<small style="padding-left:10px;color:#1686ef">(增值服务是需要跟集运路线进行绑定的，绑定了增值服务的路线在计算时将会额外计算增值服务中的费用,增值服务是管理员设置的，用户无法选择，打包服务用户可以自行选择，请区分清晰后自行设置)</small></div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('setting.addservice/add')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('setting.addservice/add') ?>">
                                            <span class="am-icon-plus"></span> 新增
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>增值服务名称</th>
                                <th>增值服务描述</th>
                                <th>增值服务模式</th>
                                <th>计费规则</th>
                                <th>操作</th>

                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['service_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['name'] ?></td>
                                        <td class="am-text-middle"><?= $item['desc'] ?></td>
                                        <td class="am-text-middle"><?= $item['type']==10?"重量模式":"长度模式" ?></td>
                                        <td class="am-text-middle">
                                            <?php if(isset($item['rule'])) : foreach(json_decode($item['rule'],true) as $item4) : ?>
                                                <?= $item['type']==10?"重量":"长度" ?>在<?= $item4['weight'][0]; ?> - <?= $item4['weight'][1]; ?> 收费:<?= $item4['weight_price']; ?><br>
                                            <?php endforeach; endif; ?>
                                        </td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('setting.addservice/edit')): ?>
                                                    <a href="<?= url('setting.addservice/edit',
                                                        ['id' => $item['service_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 编辑
                                                    </a>
                                                <?php endif; ?>
                                                <?php if (checkPrivilege('setting.addservice/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['service_id'] ?>" >
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
// 删除元素
        var url = "<?= url('store/setting.addservice/delete') ?>";
        $('.item-delete').delete('id', url);
    });
</script>

