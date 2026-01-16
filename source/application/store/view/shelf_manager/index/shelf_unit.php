<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">货位列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-u-sm-12 am-u-md-6 am-u-lg-6">
                        <div class="am-form-group">
                            <div class="am-btn-toolbar">
                                <?php if (checkPrivilege('shelf_manager.index/shelf_unit_create')): ?>
                                    <div class="am-btn-group am-btn-group-xs">
                                        <a class="am-btn am-btn-default am-btn-success am-radius"
                                           href="<?= url('shelf_manager.index/shelf_unit_create',['shelf_id'=>$shelf_id]) ?>">
                                            <span class="am-icon-plus"></span> 生成货位
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
                                <th>货位ID</th>
                                <th>货位位置</th>
                                <th>货位号</th>
                                <th>层号</th>
                                <th>货位码</th>
                                <th>货位二维码</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['shelf_unit_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf_unit_floor'] ?>层<?= $item['shelf_unit_no'] ?>号</td>
                                        <td class="am-text-middle"><?= $item['shelf_unit_no'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf_unit_floor'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf_unit_code'] ?></td>
                                        <td><a href="/<?= $item['shelf_unit_qrcode']?>"><img src="<?= $item['shelf_unit_qrcode']?>" width="80"></a></td>
                                        <td class="am-text-middle"><?= $item['created_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('shelf_manager.index/edit')): ?>
                                                    <a href="<?= url('shelf_manager.index/shelfUnitItem',
                                                        ['shelf_unit_id' => $item['shelf_unit_id']]) ?>">
                                                        <i class="am-icon-pencil"></i> 货位物品
                                                    </a>
                                                <?php endif; ?>
                                                <a href="<?= url('shelf_manager.index/shelf_updateCode',
                                                    ['shelf_unit_id' => $item['shelf_unit_id']]) ?>">
                                                    <i class="am-icon-pencil"></i> 重新生成二维码
                                                </a>
                                            
                                                <?php if (checkPrivilege('shelf_manager.index/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['shelf_unit_id'] ?>" onclick="tools.init(this)" data-mode='request' data-confirm=true data-confirm_text='请确认是否删除！' data-refresh=true data-url='<?= url('store/shelf_manager.index/deleteShelfUnit') ?>'>
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

