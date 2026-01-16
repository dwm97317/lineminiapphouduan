<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">货位物品</div>
                </div>
                <div class="widget-body am-fr">
                   <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                             <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
                                     <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="express_num"
                                                   placeholder="请输入快递单号" value="<?= $request->get('express_num') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped tpl-table-black ">
                            <thead>
                            <tr>
                                <th>存放ID</th>
                                <th>货位号ID</th>
                                <th>货物号</th>
                                <th>包裹单号</th> 
                                <th>存放货架</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): ?>
                                <?php foreach ($list as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['shelf_unit_item_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf_unit_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['pack_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['package']['express_num'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf'] ?></td>

                                        <td class="am-text-middle"><?= $item['created_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('shelf_manager.index/delete')): ?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['shelf_unit_id'] ?>" onclick="tools.init(this)" data-mode='request' data-confirm=true data-confirm_text='请确认是否下架！' data-refresh=true data-url='<?= url('store/shelf_manager.index/deleteShelfUnit') ?>'>
                                                        <i class="am-icon-trash"></i> 从货架上移除
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
                    
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(function () {

    });
</script>

