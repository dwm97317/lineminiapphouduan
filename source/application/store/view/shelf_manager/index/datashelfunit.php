<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">货位列表</div>
                </div>
                <div class="widget-body am-fr">
                     <div class="page_toolbar am-margin-bottom-xs am-cf">
                        <form class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                             <div class="am-u-sm-12 am-u-md-12">
                                <div class="am">
                                    <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('shelf_id'); ?>
                                        <select name="shelf_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '所属货架'}">
                                            <option value=""></option>
                                            <option value=" "
                                                <?= $extractShopId === ' ' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($list)): foreach ($list as $items): ?>
                                                <option value="<?= $items['id'] ?>"
                                                    <?= $items['id'] == $extractShopId ? 'selected' : '' ?>><?= $items['shelf_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                    </div>
                                     <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="express_num"
                                                   placeholder="请输入快递单号" value="<?= $request->get('express_num') ?>">
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                            <input type="text" class="am-form-field" name="search"
                                                   placeholder="请输入货位ID" value="<?= $request->get('search') ?>">
                                            <div class="am-input-group-btn">
                                                <button class="am-btn am-btn-default am-icon-search"
                                                        type="submit"></button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="am-form-group am-fl">
                                        <button type="button" class="am-btn am-btn-default" style="height:33px;line-height:15px;" data-id="" onclick="tools.init(this)" data-mode='request' data-confirm=true data-confirm_text='请确认是否全部下架！' data-refresh=true data-url='<?= url('store/shelf_manager.index/deleteAllShelfUnit') ?>'>
                                              <i class="am-icon-download"></i>
                                              一键下架
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
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
                                <th>货位ID</th>
                                <th>货位位置</th>
                                <th>所属货架</th>
                                <th>层/列</th>
                                <th>货位数据</th>
                                <th>添加时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$data->isEmpty()): ?>
                                <?php foreach ($data as $item): ?>
                                    <tr>
                                        <td class="am-text-middle"><?= $item['shelf_unit_id'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf']['shelf_name'] ?>-<?= $item['shelf']['shelf_code'] ?>-<?= $item['shelf_unit_floor'] ?>层-<?= $item['shelf_unit_no'] ?>列</td>
                                        <td class="am-text-middle"><?= $item['shelf']['shelf_name'] ?></td>
                                        <td class="am-text-middle"><?= $item['shelf_unit_floor'] ?>/<?= $item['shelf_unit_no'] ?></td>
                                        <td class="am-text-middle"><?php if (isset($item['shelfunititem']) && !$item['shelfunititem']->isEmpty() ): ?><?php foreach($item['shelfunititem'] as $_item):?>包裹单号:<?= $_item['express_num']; ?> [UID]:<?= $_item['user_id'] ;?></br><?php endforeach ;?><?php else: ?> 货位空空如也 <?php endif; ?></td>
                                        <td class="am-text-middle"><?= $item['created_time'] ?></td>
                                        <td class="am-text-middle">
                                            <div class="tpl-table-black-operation">
                                                <?php if (checkPrivilege('shelf_manager.index/shelfdelete')): ?>
                                                <?php if(isset($item['shelfunititem']) && !$item['shelfunititem']->isEmpty() ) :?>
                                                    <a href="javascript:;"
                                                       class="item-delete tpl-table-black-operation-del"
                                                       data-id="<?= $item['shelf_unit_id'] ?>" onclick="tools.init(this)" data-mode='request' data-confirm=true data-confirm_text='请确认是否删除！' data-refresh=true data-url='<?= url('store/shelf_manager.index/deleteShelfUnitItem') ?>'>
                                                        <i class="am-icon-trash"></i> 全部下架
                                                    </a>
                                                <?php endif ;?>    
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

