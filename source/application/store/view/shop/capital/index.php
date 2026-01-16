<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">结算记录</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
                                <?php if ($store['user']['is_super']==1): ?>
                                <div class="am-form-group am-fl">
                                        <?php $extractShopId = $request->get('extract_shop_id'); ?>
                                        <select name="shop_id"
                                                data-am-selected="{btnSize: 'sm', placeholder: '仓库名称'}">
                                            <option value=""></option>
                                            <option value="-1"
                                                <?= $extractShopId === ' ' ? 'selected' : '' ?>>全部
                                            </option>
                                            <?php if (isset($shopList)): foreach ($shopList as $item): ?>
                                                <option value="<?= $item['shop_id'] ?>"
                                                    <?= $item['shop_id'] == $extractShopId ? 'selected' : '' ?>><?= $item['shop_name'] ?>
                                                </option>
                                            <?php endforeach; endif; ?>
                                        </select>
                                </div>
                                <?php endif;?>
                                <div class="am-form-group tpl-form-border-form am-fl">
                                    <input type="text" name="start_time"
                                           class="am-form-field"
                                           value="<?= $request->get('start_time') ?>" placeholder="请选择起始日期"
                                           data-am-datepicker>
                                </div>
                                <div class="am-form-group tpl-form-border-form am-fl">
                                    <input type="text" name="end_time"
                                           class="am-form-field"
                                           value="<?= $request->get('end_time') ?>" placeholder="请选择截止日期"
                                           data-am-datepicker>
                                </div>
                                <div class="am-form-group am-fl">
                                    <div class="am-input-group am-input-group-sm tpl-form-border-form">
                                        <input type="text" class="am-form-field" name="search" placeholder="请输入仓库名称|联系人|手机号"
                                               value="<?= $request->get('search') ?>">
                                        <div class="am-input-group-btn">
                                            <button class="am-btn am-btn-default am-icon-search" type="submit"></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>仓库门头</th>
                                <th>仓库名称</th>
                                <th>集运单</th>
                                <th>余额变动场景</th>
                                <th>变动金额</th>
                                <th>描述/说明</th>
                                <th>创建时间</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['shop']['logo']['file_path'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $item['shop']['logo']['file_path'] ?>" width="72" height="72" alt="">
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class=""><?= $item['shop']['shop_name'] ?></p>
                                        <p class="am-link-muted">(用户ID：<?= $item['shop']['shop_id'] ?>)</p>
                                    </td>
                                    <td class="am-text-middle"><?= $item['inpack_id'] ?: '--' ?></td>
                                    <td class="am-text-middle">
                                        <span class="am-badge am-badge-secondary"><?= $item['flow_type']['text'] ?></span>
                                    </td>
                                    <td class="am-text-middle">
                                        <?= $item['flow_type']['value'] != 30 ? '+' : '' ?><?= $item['money'] ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['describe'] ?: '--' ?></td>
                                    
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                </tr>
                            <?php endforeach; else: ?>
                                <tr>
                                    <td colspan="11" class="am-text-center">暂无记录</td>
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

