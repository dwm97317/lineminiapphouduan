<div class="row-content am-cf">
    <div class="row">
        <div class="am-u-sm-12 am-u-md-12 am-u-lg-12">
            <div class="widget am-cf">
                <div class="widget-head am-cf">
                    <div class="widget-title am-cf">评价列表</div>
                </div>
                <div class="widget-body am-fr">
                    <div class="am-scrollable-horizontal am-u-sm-12">
                        <table width="100%" class="am-table am-table-compact am-table-striped
                         tpl-table-black am-text-nowrap">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th class="am-text-middle">用户</th>
                                <th>评论图片</th>
                                <th>评价路线</th>
                                <th>评分</th>
                                <th>评价内容</th>
                                <th>显示状态</th>
                                <th>评价排序</th>
                                <th>评价时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            <?php if (!$list->isEmpty()): foreach ($list as $item): ?>
                                <tr>
                                    <td class="am-text-middle"><?= $item['comment_id'] ?></td>
                                    <td class="am-text-middle">
                                        <a href="<?= $item['user']['avatarUrl'] ?>" title="点击查看大图" target="_blank">
                                            <?php if($item['user']['avatarUrl']) :?>
                                                 <img src="<?= $item['user']['avatarUrl'] ?>" width="72" height="72" alt="">
                                            <?php else:?>
                                                 <img src="assets/admin/img/head.jpg" width="72" height="72" alt="">
                                            <?php endif;?>
                                        </a>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if (!$item['image']->isEmpty()): foreach ($item['image'] as $itemd): ?>
                                        <a href="<?= $itemd['file_path'] ?>" title="点击查看大图" target="_blank">
                                            <img src="<?= $itemd['file_path'] ?>" width="50" height="50" alt="评论图片">
                                        </a>
                                        <?php endforeach;endif; ?>
                                    </td>
                                    
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= isset($item['order_m']['linedata']['name'])?$item['order_m']['linedata']['name']:'未知线路' ?></p>
                                    </td>
                                    
                                    <td class="am-text-middle">
                                        <?php if ($item['score']['all'] >=4) : ?>
                                            <span class="x-color-green">好评</span>
                                        <?php elseif ($item['score']['all'] == 3) : ?>
                                            <span class="x-color-yellow">中评</span>
                                        <?php else: ?>
                                            <span class="x-color-red">差评</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle">
                                        <p class="item-title"><?= $item['content'] ?></p>
                                    </td>
                                    <td class="am-text-middle">
                                        <?php if ($item['status']) : ?>
                                            <span class="x-color-green">显示</span>
                                        <?php else: ?>
                                            <span class="x-color-red">隐藏</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="am-text-middle"><?= $item['sort'] ?></td>
                                    <td class="am-text-middle"><?= $item['create_time'] ?></td>
                                    <td class="am-text-middle">
                                        <div class="tpl-table-black-operation">
                                            <?php if (checkPrivilege('tr_order/commentdetail')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('TrOrder/commentdetail', ['comment_id' => $item['comment_id']]) ?>">
                                                    <i class="iconfont icon-xiangqing"></i> 评论详情
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('goods.comment/delete')): ?>
                                                <a href="javascript:void(0);"
                                                   class="item-delete tpl-table-black-operation-default"
                                                   data-id="<?= $item['comment_id'] ?>">
                                                    <i class="am-icon-trash"></i> 删除
                                                </a>
                                            <?php endif; ?>
                                            <?php if (checkPrivilege('tr_order/orderdetail')): ?>
                                                <a class="tpl-table-black-operation-default"
                                                   href="<?= url('tr_order/orderdetail', ['id' => $item['order_id']]) ?>">
                                                    <i class="iconfont icon-order-o"></i> 订单详情
                                                </a>
                                            <?php endif; ?>
                                        </div>
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
        // 删除元素
        var url = "<?= url('goods.comment/delete') ?>";
        $('.item-delete').delete('comment_id', url);
    });
</script>

