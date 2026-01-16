<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">优惠券领取记录</div>
                </div>
                <div class="widget-body am-fr">
                    <!-- 工具栏 -->
                    <div class="page_toolbar am-margin-bottom-xl am-cf">
                        <form id="form-search" class="toolbar-form" action="">
                            <input type="hidden" name="s" value="/<?= $request->pathinfo() ?>">
                            <div class="am fr">
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
                                        <input type="text" class="am-form-field" name="search" placeholder="请输入用户昵称|用户ID|用户Code"
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
                                <th class="am-text-center">用户</th>
                                <th>优惠券ID</th>
                                <th>优惠券名称</th>
                                <th>优惠券类型</th>
                                <th>最低消费金额</th>
                                <th>优惠方式</th>
                                <th>有效期</th>
                                <th>领取时间</th>
                                <th>是否过期</th>
                                <th>是否使用</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-center">
                                        <p class=""><?= $item['user']['nickName'] ?></p>
                                        <?php if($set['usercode_mode']['is_show']!=1) :?>
                                        <p class="am-link-muted">(用户id：<?= $item['user']['user_id'] ?>)</p>
                                        <?php endif; ?>
                                        <?php if($set['usercode_mode']['is_show']!=0) :?>
                                        <p class="am-link-muted">(用户Code：<?= $item['user']['user_code'] ?>)</p>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['coupon_id'] ?></td>
                                    <td class="am-text-middle"><?= $item['name'] ?></td>
                                    <td class="am-text-middle"><?= $item['coupon_type']['text'] ?></td>
                                    <td class="am-text-middle"><?= $item['min_price'] ?></td>
                                    <td class="am-text-middle">
                                        <?php if ($item['coupon_type']['value'] == 10) : ?>
                                            <span>立减 <strong><?= $item['reduce_price'] ?></strong> 元</span>
                                        <?php elseif ($item['coupon_type']['value'] == 20) : ?>
                                            <span>打 <strong><?= $item['discount'] ?></strong> 折</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if ($item['expire_type'] == 10) : ?>
                                            <span>领取 <strong><?= $item['expire_day'] ?></strong> 天内有效</span>
                                        <?php elseif ($item['expire_type'] == 20) : ?>
                                            <span><?= $item['start_time']['text'] ?>
                                                ~ <?= $item['end_time']['text'] ?></span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle <?= $item['is_expire'] == 0 ? 'x-color-green' : 'x-color-red' ?>">
                                            <?= $item['is_expire']==0?'未过期':'已过期' ?>
                                    </td>
                                    <td class="am-text-middle  <?= $item['is_use'] == 1 ? 'x-color-green' : 'x-color-red' ?>">
                                        <?= $item['is_use']==0?'未使用':'已使用' ?>
                                    </td>
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

